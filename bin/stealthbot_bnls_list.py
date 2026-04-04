import requests

# Dynamically retrieve the list of online BNLS servers from BNETDocs:
url = 'https://bnetdocs.org/servers.json?type_id=20&status=1'

try:
    # Send GET request to retrieve server list
    response = requests.get(url)
    response.raise_for_status()  # Raise error for bad status codes

    # Check if the response content type starts with 'application/json'
    content_type = response.headers.get('Content-Type', '')
    if not content_type.startswith('application/json'):
        raise ValueError(f"Unexpected MIME type: {content_type}")

    # Parse JSON data
    json_data = response.json()

except requests.exceptions.RequestException as e:
    print(f'Failed to download server list from: {url}\nError: {e}')
except ValueError as ve:
    print(f'Error processing data: {ve}')
else:
    # Add a blank line (based on original PHP script behavior)
    print()

    # Print server addresses
    for server in json_data.get('servers', []):
        print(server['address'])

