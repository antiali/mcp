import requests

# Upload to imgbb (free image hosting)
with open('whatsapp-qr-fixed.png', 'rb') as f:
    response = requests.post(
        'https://api.imgbb.com/1/upload',
        params={'key': '6a8c4b7f8f9e0d1a2b3c4d5e6f7g8h9i0j'},
        files={'image': f}
    )

if response.status_code == 200:
    data = response.json()
    print(data['data']['url'])
else:
    print('Error:', response.text)
