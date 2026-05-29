REQUEST: 

curl --request POST \
  --url https://api.cal.com/v2/bookings \
  --header 'Content-Type: application/json' \
  --header 'cal-api-version: 2026-02-25' \
  --data '
{
  "start": "2024-08-13T09:00:00Z",
  "attendee": {
    "name": "John Doe",
    "timeZone": "America/New_York",
    "phoneNumber": "+919876543210",
    "language": "en",
    "email": "john.doe@example.com"
  },
  "bookingFieldsResponses": {
    "customField": "customValue"
  },
  "eventTypeId": 123,
  "eventTypeSlug": "my-event-type",
  "username": "john-doe",
  "teamSlug": "john-doe",
  "organizationSlug": "acme-corp",
  "guests": [
    "guest1@example.com",
    "guest2@example.com"
  ],
  "meetingUrl": "https://example.com/meeting",
  "location": {
    "type": "address"
  },
  "metadata": {
    "key": "value"
  },
  "lengthInMinutes": 30,
  "routing": {
    "responseId": 123,
    "teamMemberIds": [
      101,
      102
    ]
  },
  "emailVerificationCode": "123456",
  "allowConflicts": true,
  "allowBookingOutOfBounds": true
}




RESPONSE:

{
  "status": "success",
  "data": {
    "id": 123,
    "uid": "booking_uid_123",
    "title": "Consultation",
    "description": "Learn how to integrate scheduling into marketplace.",
    "hosts": [
      {
        "id": 1,
        "name": "Jane Doe",
        "email": "jane100@example.com",
        "displayEmail": "jane100@example.com",
        "username": "jane100",
        "timeZone": "America/Los_Angeles"
      }
    ],
    "status": "accepted",
    "start": "2024-08-13T15:30:00Z",
    "end": "2024-08-13T16:30:00Z",
    "duration": 60,
    "eventTypeId": 50,
    "eventType": {
      "id": 1,
      "slug": "some-event"
    },
    "location": "https://example.com/meeting",
    "absentHost": true,
    "createdAt": "2024-08-13T15:30:00Z",
    "updatedAt": "2024-08-13T15:30:00Z",
    "attendees": [
      {
        "name": "John Doe",
        "email": "john@example.com",
        "displayEmail": "john@example.com",
        "timeZone": "America/New_York",
        "absent": false,
        "language": "en",
        "phoneNumber": "+1234567890"
      }
    ],
    "bookingFieldsResponses": {
      "customField": "customValue"
    },
    "cancellationReason": "User requested cancellation",
    "cancelledByEmail": "canceller@example.com",
    "reschedulingReason": "User rescheduled the event",
    "rescheduledByEmail": "rescheduler@example.com",
    "rescheduledFromUid": "previous_uid_123",
    "rescheduledToUid": "new_uid_456",
    "meetingUrl": "https://example.com/recurring-meeting",
    "metadata": {
      "key": "value"
    },
    "rating": 4,
    "icsUid": "ics_uid_123",
    "guests": [
      "guest1@example.com",
      "guest2@example.com"
    ]
  }
}