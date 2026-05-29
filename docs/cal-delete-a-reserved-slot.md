REQUEST:
curl --request DELETE \
  --url https://api.cal.com/v2/slots/reservations/{uid} \
  --header 'Authorization: Bearer <token>' \
  --header 'cal-api-version: 2024-09-04'

  RESPONSE:

  {
  "status": "success"
}