Feature: Verb Manager
  In order to train norsk verbs
  As a Manager
  I need to add, edit or delete verbs

  Rules:
  - username is provided
  - username is valid
  - user role is manager
  - user is active
  - verb to manage is provided
  - verbId is valid to delete or edit a verb
  - verbId is known to delete or edit a verb

  Scenario: Getting a list of all verbs
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to get a list of all verbs
    Then "heinz" should get a list of all verbs

  Scenario: Get Message if database is empty
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    And the database is empty
    When "heinz" likes to get a list of all verbs
    Then "heinz" should get an error 500 '{"message":"No records found in database for: verbs"}'

  Scenario: Editing a verb successful for German and Norsk
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to edit a "german and norsk" verb with id 3
    Then the edited "german and norsk" verb "laufen, løpe" should be saved for id 3
    And No content should be returned

  Scenario: Get message if editing a non-existing verb-id
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to edit a "german" verb with id 33
    Then "heinz" should get an error 404 '{"message":"No record found in database for id: 33"}'

  Scenario: Get message if an edited version of a verb is already present for german
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to edit a verb with id 3 with an already existing "german" verb
    Then "heinz" should get an error 409 '{"message":"German verb already exists for id: 3"}'

  Scenario: Editing a verb successful for German only
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to edit a "german only" verb with id 3
    Then the edited "german only" verb "laufen" should be saved for id 3
    And No content should be returned

  Scenario: Editing a verb successful for Norsk only
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to edit a "norsk only" verb with id 3
    Then the edited "norsk only" verb "løpe" should be saved for id 3
    And No content should be returned

  Scenario: Get message if an edited version of a verb is already present for both german and norsk
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to edit a verb with id 3 with an already existing "german and norsk" verb
    Then "heinz" should get an error 409 '{"message":"Verb already exists for id: 3"}'

  @successFactor
  Scenario: Deleting a verb successful
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to delete a verb with id 3
    Then the deleted verb 3 should not be active anymore
    Then "heinz" should get a message 200 '{"message":"Removed verb with id: 3"}'

  @successFactor
  Scenario: Get message if an attempt is made to delete a missing verb
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to delete a verb with id 33
    Then "heinz" should get an error 404 '{"message":"No record found in database for id: 33"}'

  Scenario: Adding a new norsk and german verb successful
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to add "a new" verb for "german and norsk"
    Then the added verb should "be" saved
    Then "heinz" should get a message 201 '{}'

  Scenario: Adding a new german verb successful if norsk is already there, but german new
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to add "a new" verb for "german"
    Then the added verb should "be" saved
    Then "heinz" should get a message 201 '{}'

  Scenario: Try to add new norsk verb but german already exists, verb will not be saved
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to add "a new" verb for "norsk"
    Then the added verb should "not be" saved
    Then "heinz" should get an error 409 '{"message":"German verb already exists for essen | ny"}'

  Scenario: Try to add new norsk verb but german and norsk already exists and is active, nothing will be saved
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to add "an existing and active" verb for "german and norsk"
    Then the added verb should "not again be" saved
    Then "heinz" should get an error 409 '{"message":"Verb already exists for trinken | drikke"}'

  Scenario: Try to add new norsk verb but german and norsk already exists and is inactive, nothing will be saved
    Given there is a manager with the username "heinz"
    And "heinz" is an "active" manager
    When "heinz" likes to add "an existing but inactive" verb for "german and norsk"
    Then the added verb should "not be" saved
    Then "heinz" should get an error 409 '{"message":"Verb already exists for sehen | se"}'
