Feature: Verb Trainer
  In order to train norsk verbs
  As a User
  I need to train a verb and get a valid answer if my inserted verb was right or wrong

  Rules:
  - username is provided
  - username is valid
  - verb to train is provided
  - verbId is valid
  - verbId is known

  Scenario: Getting a random verb to train
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When "heinz" likes to train a verb
    Then "heinz" should get a random verb to train

  Scenario: Get Message if any word is found
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When there are no verbs in the database
    When "heinz" likes to train a verb
    Then "heinz" should get an error 500 '{"message":"No records found in database for: verbs"}' while training

  Scenario: Save result for successfully trained verb that was already trained successfully
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When heinz trained successfully a norsk verb with id "6"
    And the result is saved for "heinz"
    Then the result should be saved successfully for id "6" with "1"

  Scenario: Save result for successfully trained verb that was not yet trained successfully
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When heinz trained successfully a norsk verb with id "1"
    And the result is saved for "heinz"
    Then the result should be saved successfully for id "1" with "2"

  Scenario: Get Message if the verbId is unknown
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When heinz trained successfully "a" norsk verb with id 100
    And the result is saved for "heinz"
    Then heinz should get an error 404 '{"message":"No record found in database for verbId: 100"}' while saving

  Scenario: Training an inactive verb successfully will not save the success
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When heinz trained successfully "an inactive" norsk verb with id "10"
    Then "heinz" should get an error 404 '{"message":"No record found in database for verbId: 10"}' while saving
    And the result was not saved for id "10"

  Scenario: Get Message if the verbId is not in correct format
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When heinz trained successfully a norsk verb with id "abc"
    And the result is saved for "heinz"
    Then heinz should get an error 400 '{"message":"Id has to be numeric: abc"}' while saving

