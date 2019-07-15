Feature:
  As a referent
  In order to see adherents, committees, citizen projects of my managed area
  I should be able to access my referent space

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                             |
      | LoadCitizenProjectData                       |
      | LoadApplicationRequestRunningMateRequestData |
      | LoadApplicationRequestVolunteerRequestData   |
      | LoadJecouteSurveyData      |
      | LoadJecouteDataSurveyData  |
      | LoadJecouteDataAnswerData  |

  @javascript
  Scenario: I can see citizen projects of my managed area
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/projets-citoyens"
    Then I should see 5 "table.managed__list__table tbody tr" elements
    And I should see "Un stage pour tous"
    And I should see "Le projet citoyen à Dammarie-les-Lys"
    And I should see "En Marche - Projet citoyen"
    And I should see "Massive Open Online Course"
    And I should see "Formation en ligne ouverte à tous"

  # Municipal space
  @javascript
  Scenario: I cannot see running mate or volunteer request for the zones I don't manage
    Given I am logged as "referent-child@en-marche-dev.fr"
    When I am on "/espace-referent/candidature-colistiers"
    Then I should see "Aucun résultat" in the "#datagrid div table.managed__list__table tbody tr td" element
    When I am on "/espace-referent/candidature-benevoles"
    Then I should see "Aucun résultat" in the "#datagrid div table.managed__list__table tbody tr td" element

  @javascript
  Scenario: I can see running mate request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/candidature-colistiers"
    And I wait 5 seconds until I see NOM
    And I should see "Banner"
    And I should see "Bruce"
    And I should see "+33 6 06 06 06 06"
    And I should see "Camphin-en-Pévèle, Lille, Mons-en-Baroeul"
    And I should see "Seclin"
    And I should see "Oui"

    When I hover "table.datagrid__table-manager tbody > tr td div.action-menu-oval"
    And I follow "Plus d'infos"
    Then I should see "⟵ Retour"
    And I should see "Quelle est votre profession ?"
    And I should see "Êtes-vous engagé(e) dans une/des association(s) locale(s) ?"
    And I should see "Détails"
    And I should see "Avez-vous déjà eu un engagement militant ?"
    And I should see "Détails"
    And I should see "Avez-vous déjà exercé un mandat ?"
    And I should see "Détails"
    And I should see "Quel projet pour votre commune souhaiteriez-vous contribuer à porter ?"
    And I should see "Quel sont les atouts de votre parcours professionnel ?"

    When I follow "⟵ Retour"
    Then I should be on "/espace-referent/candidature-colistiers"

    When I wait 5 seconds until I see "TAGS DE CANDIDATURE"
    And I hover "table.datagrid__table-manager tbody > tr td div.action-menu-oval"
    And I follow "Taguer"
    Then I should see "Tags de candidature"

    When I select "4" from "application_request_tags_tags"
    And I press "Enregistrer"
    Then I wait 5 seconds until I see "TAGS DE CANDIDATURE"
    And I should see "Tag 4" in the "table.datagrid__table-manager tbody > tr td.municipal-candidate-tags" element

  @javascript
  Scenario: I can see volunteer request for the zones I manage and I can see the detail
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/candidature-benevoles"
    And I wait 5 seconds until I see NOM
    And I should see "Stark"
    And I should see "Tony"
    And I should see "Camphin-en-Pévèle, Lille, Mons-en-Baroeul"
    And I should see "Seclin"
    And I should see "Oui"

    When I hover "table.datagrid__table-manager tbody > tr td div.action-menu-oval"
    And I follow "Plus d'infos"
    Then I should see "⟵ Retour"
    And I should see "Vos thématique(s) de prédilection Sécurité Environnement"
    And I should see "Détails thématiques “Autres” Thanos destruction"
    And I should see "Disposez-vous de compétences techniques spécifiques ? Communication Management Animation Autre"
    And I should see "Avez-vous déjà participé à une campagne ? Non"
    And I should see "Détails"
    And I should see "Souhaitez-vous nous faire part de vos engagements associatifs et/ou militants ? Non"
    And I should see "Détails"

    When I follow "⟵ Retour"
    Then I should be on "/espace-referent/candidature-benevoles"

    When I wait 5 seconds until I see "TAGS DE CANDIDATURE"
    And I hover "table.datagrid__table-manager tbody > tr td div.action-menu-oval"
    And I follow "Taguer"
    Then I should see "Tags de candidature"

    When I select "4" from "application_request_tags_tags"
    And I press "Enregistrer"
    Then I wait 5 seconds until I see "TAGS DE CANDIDATURE"
    And I should see "Tag 4" in the "table.datagrid__table-manager tbody > tr td.municipal-candidate-tags" element

  @javascript
  Scenario: I can see the local surveys list, edit a survey and show the statistics
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/jecoute"
    And I should see "Questionnaires locaux"
    And I wait until I see "Questionnaire numéro 1"
    And I should not see "Un deuxième questionnaire"

    Given I click the "survey-edit-0" element
    Then I should see "Nom du questionnaire"
    And I should see "Enregistrer le questionnaire"

    Given I fill in "Nom du questionnaire" with "Questionnaire numéro 1 modifié"
    When I press "Enregistrer le questionnaire"
    And I should see "Le questionnaire a bien été mis à jour"

    Given I wait until I see "Questionnaire numéro 1 modifié"
    And I click the "survey-stats-0" element
    Then I should see "Statistiques : Questionnaire numéro 1"
    And I should see "Est-ce une question à choix multiple ?"
    And I should see "66,67 %"
    And I should see "Réponse A"

  @javascript
  Scenario: I can see the national surveys list and show the statistics
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/jecoute/questionnaires-nationaux"
    And I should see "Questionnaires nationaux"
    And I should see "Questionnaire national numéro 1"
    Given I click the "survey-stats-0" element
    Then I should see "Statistiques : Questionnaire national numéro 1"
    And I should see "Une première question du 1er questionnaire national ?"
    And I should see "Aucune donnée n'est disponible pour le moment."
