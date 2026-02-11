import requests
import hashlib
import time

# Generate unique filename
timestamp = str(int(time.time()))
filename = f"whatsapp-qr-{timestamp}.png"

# Try to upload to freeimage.host (free)
url = "https://freeimage.host/api/1/upload"
payload = {
    'key': '6d207e02198a847aa98d0a2a901485a5',  # Free public API key
    'source': open('whatsapp-qr-fixed.png', 'rb'),
    'format': 'json'
}

try:
    response = requests.post(url, files={'source': open('whatsapp-qr-fixed.png', 'rb')}, data={'key': '6d207e02198a847aa98d0a2a901485a5', 'format': 'json'})

    if response.status_code == 200:
        data = response.json()
        if data.get('status_code') == 200:
            print(data['image']['url'])
        else:
            print('Error:', data)
    else:
        print('Status:', response.status_code, response.text)
except Exception as e:
    print('Exception:', e)
