import base64

# Read the Base64 data from file
with open('whatsapp-qr.png', 'r', encoding='utf-8') as f:
    data = f.read().strip()

# Decode Base64 to image data
img_data = base64.b64decode(data)

# Save as proper PNG file
with open('whatsapp-qr-fixed.png', 'wb') as f:
    f.write(img_data)

print('QR code image saved as whatsapp-qr-fixed.png')
