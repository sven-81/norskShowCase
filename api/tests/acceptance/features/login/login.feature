# features/login.feature
Feature: User Login
  In order to train and manage norsk words and verbs
  As a User
  I need to be able to login and logout

  Rules:
  - username is provided
  - username is valid
  - password is provided
  - password is valid

  Scenario: Login as a registered and active user
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When I provide the correct password for "heinz"
    And I login with the username "heinz"
    Then I should have been logged in as "heinz"

  Scenario: Login attempt as a registered and active user with wrong password causes a 401
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When I provide the wrong password for "heinz"
    And I login with the username heinz
    Then I should not have been logged in as "heinz"

  Scenario: Login attempt as a registered, but inactive user causes a 403
    Given there is a user with the username "karl"
    And "karl" is an "inactive" user
    When I provide the correct password for "karl"
    And I login with the username karl
    Then I should not have been logged in as karl since forbidden

  Scenario: Login attempt as a unregistered user causes a 401
    Given there is no user with the username klaus
    When I provide the correct password for "klaus"
    And I login with the username klaus
    Then I should not have been logged in as "klaus"

  Scenario: Login attempt without username
    Given request is missing username
    When I login
    Then I should get an error 400 '{"message":"Missing required parameter: userName"}'

  Scenario: Login attempt without password
    Given request is missing password
    When I login
    Then I should get an error 400 '{"message":"Missing required parameter: password"}'
