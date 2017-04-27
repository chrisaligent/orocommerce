@fixture-product_collections.yml
Feature:
  In order to add more than one product by some criteria into the content nodes
  As an Administrator
  I want to have ability of adding Product Collection variant

  Scenario: Logged in as buyer and manager on different window sessions
    Given sessions active:
     | Admin  | first_session  |
     | Buyer  | second_session |
    And I set "Default Web Catalog" as default web catalog

  Scenario: Add Product Collection variant
    Given I proceed as the Admin
    And I login as administrator
    When I go to Marketing/Web Catalogs
    And I click "Edit Content Tree" on "Default Web Catalog" in grid
    And I click on "Show Variants Dropdown"
    And I click "Add Product Collection"
    And I click "Content Variants"
    Then I should see "Product Collection"
    And I should not see an "Product Collection Preview Grid" element

  Scenario: Apply filters
    When I drag and drop "Field Condition" on "Drop condition here"
    And I click "Choose a field.."
    And I click on "SKU"
    And type "PSKU1" in "value"
    And I click on "Apply the Query"
    And I should see following grid:
      | SKU   | NAME      |
      | PSKU1 | Product 1 |

  Scenario: Save Product Collection with defined filters and applied query
    When I click "Save"
    Then I should not see text matching "You have changes in the Filters section that have not been applied"
    Then I reload the page
    Then I should see following grid:
      | SKU   | NAME      |
      | PSKU1 | Product 1 |

  Scenario: Created Product Collection accessible at frontend
    Given I operate as the Buyer
    And I am on homepage
    Then I should see "PSKU1"
    And I should not see "PSKU2"
    And Page title equals to "Root Node"
    And Page meta keywords equals "CollectionMetaKeyword"
    And Page meta description equals "CollectionMetaDescription"

  Scenario: Autogenerated for Product Collection segments available in Manage Segments section
    Given I proceed as the Admin
    When I go to Reports & Segments/ Manage Segments
    Then I should see "Auto generated" in grid with following data:
      | Entity                  | Product               |
      | Type                    | Dynamic               |
    When I click on Auto generated in grid
    Then I should not see an "Entity Edit Button" element
    And I should not see an "Entity Delete Button" element

  Scenario: Product Collection can be edited
    When I go to Marketing/Web Catalogs
    And I click "Edit Content Tree" on "Default Web Catalog" in grid
    And I click "Content Variants"
    And type "PSKU" in "value"
    And I click on "Apply the Query"
    Then I should see following grid:
      | SKU   | NAME      |
      | PSKU2 | Product 2 |
      | PSKU1 | Product 1 |

  Scenario: Edited Product Collection can be saved
    When I click "Save"
    And I reload the page
    Then I should see following grid:
      | SKU   | NAME      |
      | PSKU2 | Product 2 |
      | PSKU1 | Product 1 |

  Scenario: Edited Product Collection accessible at frontend
    Given I operate as the Buyer
    And I am on homepage
    Then I should see "PSKU1"
    And I should see "PSKU2"

  Scenario: Change "Product 2" SKU in order to exclude it from product collection filter
    Given I operate as the Admin
    And go to Products/ Products
    And I click view Product 2 in grid
    And I click "Edit"
    And I fill form with:
      | SKU | XSKU |
    And I save and close form
    Then I should see "Product has been saved" flash message

  Scenario: "Product 2" that already not confirm to filter, excluded from product collection grid at backend
    When I go to Marketing/Web Catalogs
    And I click "Edit Content Tree" on "Default Web Catalog" in grid
    Then I should see following grid:
      | SKU   | NAME      |
      | PSKU1 | Product 1 |

  Scenario: "Product 2" that already not confirm to filter, excluded from product collection grid at frontend
    Given I operate as the Buyer
    And I am on homepage
    Then I should see "PSKU1"
    And I should not see "PSKU2"

  Scenario: Change "Product 2" SKU in order to include it to the product collection filter again
    Given I operate as the Admin
    And go to Products/ Products
    And I click view Product 2 in grid
    And I click "Edit"
    And I fill form with:
      | SKU | PSKU2 |
    And I save and close form
    Then I should see "Product has been saved" flash message

  Scenario: "Product 2" that confirm to filter again, included into product collection grid at backend
    When I go to Marketing/Web Catalogs
    And I click "Edit Content Tree" on "Default Web Catalog" in grid
    Then I should see following grid:
      | SKU   | NAME      |
      | PSKU2 | Product 2 |
      | PSKU1 | Product 1 |

  Scenario: "Product 2" that confirm to filter again, included into product collection grid at frontend
    Given I operate as the Buyer
    And I am on homepage
    Then I should see "PSKU1"
    And I should see "PSKU2"

  Scenario: Confirmation cancel after save changed not applied filters
    Given I proceed as the Admin
    And I click "Content Variants"
    When type "PSKU1" in "value"
    And I click "Save"
    Then I should see text matching "You have changes in the Filters section that have not been applied"
    And I click "Cancel" in modal window
    And I should see following grid:
      | SKU   | NAME      |
      | PSKU2 | Product 2 |
      | PSKU1 | Product 1 |

  Scenario: Confirmation accept after save changed not applied filters
    When I click "Content Variants"
    And type "PSKU1" in "value"
    And I click "Save"
    Then I should see text matching "You have changes in the Filters section that have not been applied"
    And I click "Continue" in modal window
    And I should see following grid:
      | SKU   | NAME      |
      | PSKU1 | Product 1 |

  Scenario: Confirmation cancel, after save changed not applied filters several product collections
    When I click "Content Variants"
    And type "PSKU2" in "value"
    Then I click on "Show Variants Dropdown"
    And I click "Add Product Collection"
    And I click "Content Variants"
    Then I should see 2 elements "Product Collection Variant Label"
    And I click "Save"
    Then I should see text matching "You have changes in the Filters section that have not been applied"
    And I click "Cancel" in modal window
    Then I should not see text matching "You have changes in the Filters section that have not been applied"

  Scenario: Reset Product Collection after filters change
    Given I reload the page
    And I click "Content Variants"
    When type "TEST" in "value"
    And I click "Apply the Query"
    And I click on "Reset"
    Then I should not see "TEST"
    And I click "Apply the Query"
    Then I should see "PSKU1"
    And I click "Cancel"

  Scenario: Content Node changes are reflected on frontend
    When I go to Marketing/Web Catalogs
    And I click "Edit Content Tree" on "Default Web Catalog" in grid
    And I fill "Content Node Form" with:
      | Meta Keywords    | AnotherCollectionMetaKeyword     |
      | Meta Description | AnotherCollectionMetaDescription |
    And I save form
    Then I should see "Content Node has been saved" flash message

  Scenario: Products which belong to product collection are searchable by Content Node meta information for this Product Collection
    Given I operate as the Buyer
    When I am on homepage
    Then Page meta keywords equals "AnotherCollectionMetaKeyword"
    And Page meta description equals "AnotherCollectionMetaDescription"
    When I fill in "search" with "AnotherCollectionMetaKeyword"
    And I press "Search Button"
    Then I should see "PSKU1"

  Scenario: Products Collection is deletable
    Given I proceed as the Admin
    And I go to Marketing/Web Catalogs
    And I click "Edit Content Tree" on "Default Web Catalog" in grid
    When I click on "Remove Variant Button"
    Then I should not see "Product Collection"
    When I click on "Show Variants Dropdown"
    And I click "Add System Page"
    When I save form
    Then I should see "Content Node has been saved" flash message

  Scenario: Products are not searchable by Content Node meta information for deleted Product Collection
    Given I proceed as the Buyer
    And I am on homepage
    When I fill in "search" with "AnotherCollectionMetaKeyword"
    And I press "Search Button"
    Then I should not see "PSKU1"
