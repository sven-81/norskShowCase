# features/registration.feature
Feature: User Registration
  In order to train and manage norsk words and verbs
  As a User
  I need to be able to register

  Rules:
  - username is provided
  - username is unique
  - username is not empty
  - firstname is provided
  - lastname is provided
  - password is provided
  - password consists at least of 12 chars

  Scenario: Register as a new user with free username
    Given there is no user yet with the username "Klaus"
    When I register with the username Klaus
    Then I should have been registered as "Klaus"

  Scenario: Register as a new user with an username that is already taken
    Given there is already a user with the username "heinz"
    When I register with the taken username heinz
    Then I should get an error 409 '{}'

  Scenario: Register as a new user without username
    Given request is missing username
    When I register
    Then I should get an error 400 '{"message":"Missing required parameter: username"}'

  Scenario: Register as a new user without firstname
    Given request is missing firstname
    When I register
    Then I should get an error 400 '{"message":"Missing required parameter: firstName"}'

  Scenario: Register as a new user without lastname
    Given request is missing lastname
    When I register
    Then I should get an error 400 '{"message":"Missing required parameter: lastName"}'

  Scenario: Register as a new user without password
    Given request is missing password
    When I register
    Then I should get an error 400 '{"message":"Missing required parameter: password"}'

  Scenario: Register as a new user with too short password
    Given request has a short password
    When I register
    Then I should get an error 422 '{"message":"The password must be at least 12 characters long."}'
