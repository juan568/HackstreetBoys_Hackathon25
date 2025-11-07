import requests
import json
import time
import os
from datetime import datetime, timezone

# Configuración local
CACHE_DIR = 'cache/'
FINAL_JSON_FILE = os.path.join(CACHE_DIR, 'dashboard_data.json')
CACHE_TTL_REALTIME = 60

# API OpenSky sin autenticación
URL_REALTIME_STATES = 'https://opensky-network.org/api/states/all'

# Coordenadas CDMX
LAT_MIN, LAT_MAX = 19.2, 19.6
LON_MIN, LON_MAX = -99.3, -99.0

# Configuración Zabbix Local
ZABBIX_SERVER = 'localhost'
ZABBIX_PORT = 10051
ZABBIX_HOST = 'Monitor-Vuelos-CDMX'

def get_data_with_cache(cache_key, url, ttl_seconds):
    """Obtiene datos de la API usando caché (sin autenticación)."""
    if not os.path.exists(CACHE_DIR):
        os.makedirs(CACHE_DIR, exist_ok=True)
        
    cache_file = os.path.join(CACHE_DIR, f"{hash(cache_key)}.json")

    if os.path.exists(cache_file) and (time.time() - os.path.getmtime(cache_file)) < ttl_seconds:
        try:
            with open(cache_file, 'r') as f:
                print(f"[{cache_key}] Usando datos en caché...")
                return json.load(f)
        except Exception:
            pass

    print(f"[{cache_key}] Consultando API OpenSky...")
    
    try:
        response = requests.get(url, timeout=15)
        
        if response.status_code == 429:
            print("⚠ Límite de peticiones alcanzado (429). Usando caché si existe.")
            if os.path.exists(cache_file):
                with open(cache_file, 'r') as f:
                    return json.load(f)
            return None
            
        response.raise_for_status()
        api_data = response.json()
        
        with open(cache_file, 'w') as f:
            json.dump(api_data, f)
        
        return api_data
        
    except requests.exceptions.RequestException as e:
        print(f"❌ Error en la API: {e}")
        return None

def send_to_zabbix_local(data_json):
    """Envía datos a Zabbix local usando py-zabbix."""
    try:
        from pyzabbix import ZabbixMetric, ZabbixSender
        
        total_flights = data_json['status']['total_flights']
        
        metrics = [
            ZabbixMetric(ZABBIX_HOST, 'vuelos.total', total_flights)
        ]
        
        sender = ZabbixSender(ZABBIX_SERVER, ZABBIX_PORT)
        result = sender.send(metrics)
        
        print(f"✅ ZABBIX LOCAL: Enviados {total_flights} vuelos. Resultado: {result}")
        
    except ImportError:
        print("⚠ py-zabbix no instalado. Ejecuta: pip install py-zabbix")
    except Exception as e:
        print(f"❌ Error al enviar a Zabbix: {e}")

def run_tracker():
    """Lógica principal para obtener, procesar, guardar JSON y enviar a Zabbix local."""
    
    # Construir URL con coordenadas CDMX
    url_with_coords = f"{URL_REALTIME_STATES}?lamin={LAT_MIN}&lomin={LON_MIN}&lamax={LAT_MAX}&lomax={LON_MAX}"
    
    realtime_data = get_data_with_cache('realtime_states_cdmx', url_with_coords, CACHE_TTL_REALTIME)

    flights_list = []
    
    if realtime_data and 'states' in realtime_data and realtime_data['states']:
        status_api = "Activo"
        alert_msg = ""
        vuelos = realtime_data['states']
    else:
        status_api = "Sin datos"
        alert_msg = "No hay vuelos en la zona CDMX o límite de API alcanzado."
        vuelos = []

    # Procesar vuelos
    for state in vuelos:
        icao24 = str(state[0]).strip()
        callsign = str(state[1]).strip() if state[1] else "N/A"
        origin_country = state[2] if state[2] else "N/A"
        latitude = state[6]
        longitude = state[5]
        geo_altitude = state[13] if state[13] else state[7]
        on_ground = state[8]
        velocity = state[9]
        true_track = state[10]

        if latitude is None or longitude is None:
            continue
        
        flights_list.append({
            'icao24': icao24,
            'callsign': callsign,
            'origin_country': origin_country,
            'lat': latitude,
            'lon': longitude,
            'altitude': round(geo_altitude) if geo_altitude is not None else 0,
            'velocity': round(velocity) if velocity is not None else 0,
            'heading': round(true_track) if true_track is not None else 0,
            'on_ground': bool(on_ground)
        })
    
    # Crear JSON final
    final_output = {
        'flights': flights_list,
        'status': {
            'api_status': status_api,
            'last_update': datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
            'alert': alert_msg,
            'total_flights': len(flights_list)
        }
    }

    try:
        if not os.path.exists(CACHE_DIR):
            os.makedirs(CACHE_DIR, exist_ok=True)
            
        with open(FINAL_JSON_FILE, 'w') as f:
            json.dump(final_output, f, indent=4)
        print(f"\n✅ Datos guardados en: {FINAL_JSON_FILE}")
        print(f"   Vuelos detectados en CDMX: {len(flights_list)}")
        
        # Enviar a Zabbix local
        send_to_zabbix_local(final_output)

    except Exception as e:
        print(f"\n❌ Error al guardar el JSON: {e}")

if __name__ == "__main__":
    print("=== Tracker de Vuelos CDMX - Modo Local ===")
    run_tracker()