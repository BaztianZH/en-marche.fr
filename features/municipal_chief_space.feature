Feature:
  As a municipal chief
  In order to see application request of my managed area
  I should be able to access my municipal chief space

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                             |
      | LoadApplicationRequestRunningMateRequestData |
      | LoadApplicationRequestVolunteerRequestData   |

  @javascript
  Scenario: I can see running mate request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "municipal-chief@en-marche-dev.fr"
    When I am on "/espace-chef-municipal/municipale/candidature-colistiers"
    Then I should see "Vous gérez : Lille, Oignies, Seclin"
    And I should see 4 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "Camphin-en-Carembault, Lille"
    And I should see "Camphin-en-Pévèle, Lille, Mons-en-Baroeul"
    And I should see "Mons-en-Pévèle, Seclin"
    And I should see "Seclin"

    When I hover "table.datagrid__table-manager tbody > tr td div.action-menu-oval"
    And I follow "Plus d'infos"
    Then I should see "⟵ Retour"
    And I should see "Profession Scientist"
    And I should see "Membre de l'association locale ? Non"
    And I should see "Domaine de l'association locale Fighting super villains"
    And I should see "Activiste politique ? Non"
    And I should see "Activiste politique détails Putsch Thanos from his galactic throne"
    And I should see "Est l'élu précédent ? Non"
    And I should see "Est l'élu précédent détails"
    And I should see "Détails du projet"
    And I should see "Actifs professionnels"

    When I follow "⟵ Retour"
    Then I should be on "/espace-chef-municipal/municipale/candidature-colistiers"

    When I hover "table.datagrid__table-manager tbody > tr td div.action-menu-oval"
    And I follow "Taguer"
    Then I should see "Tags de candidature"

    When I select "4" from "application_request_tags_tags"
    And I press "Enregistrer"
    Then the 5 column of the 1 row in the table.managed__list__table table should contain "Tag 4"

  @javascript
  Scenario Outline: I can see running mate request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "<user>"
    When I am on "/espace-chef-municipal/municipale/candidature-colistiers"
    Then I should see "<managed-cities>"
    And I should see 2 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "<cities-tr-1>"
    And I should see "<cities-tr-2>"
    And I should not see "<missing-city>"
    And I should not see "<missing-tag>"

    When I am on "/espace-chef-municipal/municipale/candidature-colistiers/<forbidden-uuid>/detail"
    Then I should see "Détails du candidat"

    When I am on "/espace-chef-municipal/municipale/candidature-colistiers/<forbidden-uuid>/editer-tags"
    Then I should see "Tags de candidature"

    Examples:
      | user                               | managed-cities                                        | cities-tr-1                               | cities-tr-2                               | missing-city           | missing-tag | forbidden-uuid                       |
      | municipal-chief-2@en-marche-dev.fr | Vous gérez : Camphin-en-Carembault, Camphin-en-Pévèle | Camphin-en-Carembault, Lille              | Camphin-en-Pévèle, Lille, Mons-en-Baroeul | Seclin                 | Tag 4       | b1f336d8-5a33-4e79-bf02-ae03d1101093 |
      | municipal-chief-3@en-marche-dev.fr | Vous gérez : Mons-en-Baroeul, Mons-en-Pévèle          | Camphin-en-Pévèle, Lille, Mons-en-Baroeul | Mons-en-Pévèle, Seclin                    | Camphin-en-Carembault  | Tag 1       | 23db4b50-dbe3-4b7f-9bd8-f3eaba8367de |

  @javascript
  Scenario: I can see volunteer request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "municipal-chief@en-marche-dev.fr"
    When I am on "/espace-chef-municipal/municipale/candidature-benevole"
    Then I should see "Vous gérez : Lille, Oignies, Seclin"
    And I should see 4 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "Camphin-en-Carembault, Lille"
    And I should see "Mons-en-Pévèle, Seclin"
    And I should see "Seclin"

    When I hover "table.datagrid__table-manager tbody > tr td div.action-menu-oval"
    And I follow "Plus d'infos"
    Then I should see "⟵ Retour"
    And I should see "Thèmes favoris Sécurité Environnement"
    And I should see "Thèmes favoris personnalisés Thanos destruction"
    And I should see "Compétences techniques Communication Management Animation Autre"
    And I should see "Fait partie d'une précédente campagne ? Non"
    And I should see "Domaine de l'association locale"
    And I should see "Partage l'engagement associatif ? Non"
    And I should see "Détail de l'engagement associatif"

    When I follow "⟵ Retour"
    Then I should be on "/espace-chef-municipal/municipale/candidature-benevole"

    When I hover "table.datagrid__table-manager tbody > tr td div.action-menu-oval"
    And I follow "Taguer"
    Then I should see "Tags de candidature"

    When I select "4" from "application_request_tags_tags"
    And I press "Enregistrer"
    Then the 5 column of the 1 row in the table.managed__list__table table should contain "Tag 4"

  @javascript
  Scenario Outline: I can see volunteer request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "<user>"
    When I am on "/espace-chef-municipal/municipale/candidature-benevole"
    Then I should see "<managed-cities>"
    And I should see 2 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "<cities-tr-1>"
    And I should see "<cities-tr-2>"
    And I should not see "<missing-city>"
    And I should not see "<missing-tag>"

    When I am on "/espace-chef-municipal/municipale/candidature-benevole/<forbidden-uuid>/detail"
    Then I should see "Détails du candidat"

    When I am on "/espace-chef-municipal/municipale/candidature-benevole/<forbidden-uuid>/editer-tags"
    Then I should see "Tags de candidature"

    Examples:
      | user                               | managed-cities                                        | cities-tr-1                               | cities-tr-2                               | missing-city           | missing-tag | forbidden-uuid                       |
      | municipal-chief-2@en-marche-dev.fr | Vous gérez : Camphin-en-Carembault, Camphin-en-Pévèle | Camphin-en-Carembault, Lille              | Camphin-en-Pévèle, Lille, Mons-en-Baroeul | Seclin                 | Tag 4       | 5ca5fc5c-b6f4-4edf-bb8e-111aa9222696 |
      | municipal-chief-3@en-marche-dev.fr | Vous gérez : Mons-en-Baroeul, Mons-en-Pévèle          | Camphin-en-Pévèle, Lille, Mons-en-Baroeul | Mons-en-Pévèle, Seclin                    | Camphin-en-Carembault  | Tag 1       | 5ca5fc5c-b6f4-4edf-bb8e-111aa9222696 |

  @javascript
  Scenario Outline: I can define application request as added to my team
    Given I am logged as "municipal-chief-2@en-marche-dev.fr"
    When I am on "<url>"
    And I hover "table.datagrid__table-manager tbody > tr td div.action-menu-oval"
    And I follow "Ajouter à mon équipe"
    Then I hover "table.datagrid__table-manager tbody > tr td div.action-menu-oval"
    And I should see "Retirer de mon équipe"
    And I should not see an ".link--disabled" element

    When I am on "/deconnexion"
    And I am logged as "municipal-chief@en-marche-dev.fr"
    And I am on "<url>"
    And I hover "table.datagrid__table-manager tbody > tr td div.action-menu-oval"
    Then I should see "Retirer de mon équipe"
    And I should see an ".link--disabled" element

    Examples:
      | url                                                      |
      | /espace-chef-municipal/municipale/candidature-benevole   |
      | /espace-chef-municipal/municipale/candidature-colistiers |

  Scenario Outline: I list adherent living in the cities I manage
    Given I am logged as "<user>"
    And I am on "/espace-chef-municipal/adherents"
    And I wait 10 seconds until I see "Identité"
    Then I should see "<shouldSee>"
    And I should not see "<shouldNotSee>"

    Examples:
      | user                               | shouldSee         | shouldNotSee      |
      | municipal-chief@en-marche-dev.fr   | Dusse Jean-Claude | Morin Bernard     |
      | municipal-chief-3@en-marche-dev.fr | Morin Bernard     | Dusse Jean-Claude |
