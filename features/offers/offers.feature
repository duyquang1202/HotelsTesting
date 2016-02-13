Feature: Offers public API
    In order to control Offers from anywhere
    As an API user
    I need to be able to find rooms

@find_rooms
Scenario: 1.  I can find room-names at "The Reverie Residence"
    When I request "/api/offers/find?date=2017-02-27" with method "get" and content type is "form"
    Then The response should be JSON
    Then HTTP status code should be 200
    When Found records
    Then I should see "1 Bedroom Classic"
