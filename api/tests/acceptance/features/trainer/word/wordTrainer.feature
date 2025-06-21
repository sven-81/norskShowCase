Feature: Word Trainer
  In order to train norsk words
  As a User
  I need to train a word and get a valid answer if my inserted word was right or wrong

  Rules:
  - username is provided
  - username is valid
  - word to train is provided
  - wordId is valid
  - wordId is known

  Scenario: Getting a random word to train
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When I like to train a word as "heinz"
    Then I should get a random word to train

  Scenario: Get Message if any word is found
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When there are no words in the database
    And I like to train a word as "heinz"
    Then heinz should get an error 500 '{"message":"No records found in database for: words"}' while training

  Scenario: Save result for successfully trained word that was already trained successfully
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When heinz trained successfully a norsk word with id "6"
    And the result is saved
    Then the result should be saved successfully for id "6" with "1"

  Scenario: Save result for successfully trained word that was not yet trained successfully
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When heinz trained successfully a norsk word with id "1"
    And the result is saved
    Then the result should be saved successfully for id "1" with "2"

  Scenario: Get Message if the wordId is unknown
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When heinz trained successfully "a" norsk word with id 100
    And the result is saved
    Then heinz should get an error 404 '{"message":"No record found in database for wordId: 100"}' while saving

  Scenario: Training an inactive word successfully will not save the success
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When heinz trained successfully "an inactive" norsk word with id "10"
    Then heinz should get an error 404 '{"message":"No record found in database for wordId: 10"}' while saving
    And the result was not saved for id "10"

  Scenario: Get Message if the wordId is not in correct format
    Given there is a user with the username "heinz"
    And "heinz" is an "active" user
    When heinz trained successfully a norsk word with id "abc"
    And the result is saved
    Then heinz should get an error 400 '{"message":"Id has to be numeric: abc"}' while saving

