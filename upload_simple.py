import requests
import sys

# Upload to catbox.moe
with open('whatsapp-qr-fixed.png', 'rb') as f:
    files = {'file': f}
    response = requests.post('https://catbox.moe/user/api.php', files=files)

if response.status_code == 200:
    url = response.text.strip()
    print('Image URL:', url)
else:
    print('Error:', response.text)
