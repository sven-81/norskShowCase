Feature: Word Manager
  In order to train norsk words
  As a Manager
  I need to add, edit or delete words

  Rules:
  - username is provided
  - username is valid
  - user role is manager
  - user is active
  - word to manage is provided
  - wordId is valid to delete or edit a word
  - wordId is known to delete or edit a word

  Scenario: Getting a list of all words
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to get a list of all words as "heinz"
    Then I should get a list of all words

  Scenario: Get Message if database is empty
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    And the database is empty
    When I like to get a list of all words as "heinz"
    Then heinz should get an error 500 '{"message":"No records found in database for: words"}'

  Scenario: Editing a word successful for German and Norsk
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to edit a "german and norsk" word with id 3
    Then the edited "german and norsk" word "neu, ny" should be saved for id 3
    And No content should be returned

  Scenario: Get message if editing a non-existing word-id
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to edit a "german" word with id 33
    Then heinz should get an error 404 '{"message":"No record found in database for id: 33"}'

  Scenario: Get no message if an edited version of a word is already present for norsk and save it
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to edit a word with id 3 with an already existing "norsk" word "in indifferent case"
    Then the edited "norsk" word "kjærlighet" should be saved for id 3
    And No content should be returned

  Scenario: Editing a word successful for German only
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to edit a "german only" word with id 3
    Then the edited "german only" word "neu" should be saved for id 3
    And No content should be returned

  Scenario: Editing a word successful for Norsk only
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to edit a "norsk only" word with id 3
    Then the edited "norsk only" word "ny" should be saved for id 3
    And No content should be returned

  Scenario: Get message if an edited version of a word is already present for german in same case
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to edit a word with id 3 with an already existing "german" word "in same case"
    Then heinz should get an error 409 '{"message":"German word already exists for id: 3"}'

  Scenario: Get message if an edited version of a word is already present for german case contrary
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to edit a word with id 3 with an already existing "german" word "case contrary"
    Then the edited "german" word "liebe" should be saved for id 3
    And No content should be returned

  Scenario: Get message if an edited version of a word is already present for both german and norsk
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to edit a word with id 3 with an already existing "german and norsk" word "in indifferent case"
    Then heinz should get an error 409 '{"message":"Word already exists for id: 3"}'

  @successFactor
  Scenario: Deleting a word successful
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to delete a word with id 3
    Then the deleted word 3 should not be active anymore
    Then heinz should get a message 200 '{"message":"Removed word with id: 3"}'

  @successFactor
  Scenario: Get message if an attempt is made to delete a missing word
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to delete a word with id 33
    Then heinz should get an error 404 '{"message":"No record found in database for id: 33"}'

  Scenario: Adding a new norsk and german word successful
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to add "a new" word for "german and norsk"
    Then the added word should "be" saved
    Then heinz should get a message 201 '{}'

  Scenario: Adding a new german word successful if norsk is already there, but german new
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to add "a new" word for "german"
    Then the added word should "be" saved
    Then heinz should get a message 201 '{}'

  Scenario: Try to add new norsk word but german already exists in lowercase, word will be saved
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to add "an existing in lowercase and active" word for "german"
    Then the added word should "be" saved
    Then heinz should get a message 201 '{}'

  Scenario: Try to add new norsk word but german already exists, word will not be saved
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to add "a new" word for "norsk"
    Then the added word should "not be" saved
    Then heinz should get an error 409 '{"message":"German word already exists for \"{\"german\":\"Wellen\",\"norsk\":\"ny\"}\""}'

  Scenario: Try to add new norsk word but german and norsk already exists and is active, nothing will be saved
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to add "an existing and active" word for "german and norsk"
    Then the added word should "not again be" saved
    Then heinz should get an error 409 '{"message":"Word already exists for \"{\"german\":\"Wellen\",\"norsk\":\"b\\u00f8lger\"}\""}'

  Scenario: Try to add new norsk word but german and norsk already exists and is inactive, nothing will be saved
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When I like to add "an existing but inactive" word for "german and norsk"
    Then the added word should "not be" saved
    Then heinz should get an error 409 '{"message":"Word already exists for \"{\"german\":\"Gr\\u00fcn\",\"norsk\":\"gr\\u00f8nn\"}\""}'
