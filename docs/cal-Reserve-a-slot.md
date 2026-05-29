REQUEST:

curl --request POST \
  --url https://api.cal.com/v2/slots/reservations \
  --header 'Content-Type: application/json' \
  --header 'cal-api-version: 2024-09-04' \
  --data '
{
  "eventTypeId": 1,
  "slotStart": "2024-09-04T09:00:00Z",
  "slotDuration": 30,
  "reservationDuration": 5
}
'


RESPONSE:

{
  "status": "success",
  "data": {
    "eventTypeId": 1,
    "slotStart": "2024-09-04T09:00:00Z",
    "slotEnd": "2024-09-04T10:00:00Z",
    "slotDuration": 30,
    "reservationUid": "e84be5a3-4696-49e3-acc7-b2f3999c3b94",
    "reservationDuration": 5,
    "reservationUntil": "2023-09-04T10:00:00Z"
  }
}