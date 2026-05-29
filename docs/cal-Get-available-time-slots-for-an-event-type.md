REQUEST:

curl --request GET \
  --url https://api.cal.com/v2/slots \
  --header 'Authorization: Bearer <token>' \
  --header 'cal-api-version: 2024-09-04'


  RESPONSE: 

  {
  "status": "success",
  "data": {
    "2050-09-05": [
      {
        "start": "2050-09-05T09:00:00.000+02:00"
      },
      {
        "start": "2050-09-05T10:00:00.000+02:00"
      }
    ],
    "2050-09-06": [
      {
        "start": "2050-09-06T09:00:00.000+02:00"
      },
      {
        "start": "2050-09-06T10:00:00.000+02:00"
      }
    ]
  }
}