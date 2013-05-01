<?php
// We'll use the unittest modules bootstrap to hook into Kohana
require_once __DIR__.'/../../../../modules/unittest/bootstrap.php';

use Behat\Behat\Context\ClosuredContextInterface,
	Behat\Behat\Context\TranslatedContextInterface,
	Behat\Behat\Context\BehatContext,
	Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
	Behat\Gherkin\Node\TableNode;

require_once 'RestContext.php';

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
	/**
	 * Initializes context.
	 * Every scenario gets it's own context object.
	 *
	 * @param   array   $parameters     context parameters (set them up through behat.yml)
	 */
	public function __construct(array $parameters)
	{
		// Initialize your context here
		$this->useContext('RestContext', new RestContext($parameters));
	}

	/** @BeforeSuite */
	public static function setup($event)
	{
		// Unnecessary but leaving it anyway
		
		// Clean the DB before we start
		self::_clean_db();
	}

	/** @BeforeFeature */
	public static function featureSetup($event)
	{
		// this might need to run before every scenario, but
		// that would be a bit slow
		
		// Clean the DB before we start
		self::_clean_db();
		
		// Create Dummy form
		ORM::factory("Form")
			->set('name', 'Dummy')
			->set('type', 'report')
			->set('description', 'Dummy')
			->set('id', 1)
			->save();
			
		// Create Dummy groups
		ORM::factory("Form_Group")
			->set('label', 'Dummy')
			->set('priority', 99)
			->set('form_id', 1)
			->set('id', 1)
			->save();
			
		// Create Dummy attribute
		ORM::factory("Form_Attribute")
			->set('key', 'dummy_varchar')
			->set("label", "Dummy")
			->set("type", "varchar")
			->set("input", "text")
			->set("required", true)
			->set("priority", 1)
			->set('form_id', 1)
			->set('form_group_id', 1)
			->set('id', 1)
			->save();
	}

	/**
	 * @BeforeFeature @post
	 */
	public static function setupFormForPost($event)
	{
		
		// Create full_name attribute
		ORM::factory("Form_Attribute")
			->set('key', 'full_name')
			->set("label", "Full Name")
			->set("type", "varchar")
			->set("input", "text")
			->set("required", true)
			->set("priority", 1)
			->set('form_id', 1)
			->set('form_group_id', 1)
			->save();
		
		// Create description attribute
		ORM::factory("Form_Attribute")
			->set('key', 'description')
			->set("label", "Description")
			->set("type", "text")
			->set("input", "textarea")
			->set("required", true)
			->set("priority", 2)
			->set('form_id', 1)
			->set('form_group_id', 1)
			->save();
		
		// Create dob attribute
		ORM::factory("Form_Attribute")
			->set('key', 'date_of_birth')
			->set("label", "Date of birth")
			->set("type", "datetime")
			->set("input", "date")
			->set("required", false)
			->set("priority", 3)
			->set('form_id', 1)
			->set('form_group_id', 1)
			->save();
		
		// Create missing_date attribute
		ORM::factory("Form_Attribute")
			->set('key', 'missing_date')
			->set("label", "Missing Date")
			->set("type", "datetime")
			->set("input", "date")
			->set("required", true)
			->set("priority", 4)
			->set('form_id', 1)
			->set('form_group_id', 1)
			->save();
		
		// Create last_location attribute
		ORM::factory("Form_Attribute")
			->set('key', 'last_location')
			->set("label", "Last Location")
			->set("type", "varchar")
			->set("input", "text")
			->set("required", true)
			->set("priority", 5)
			->set('form_id', 1)
			->set('form_group_id', 1)
			->save();
		
		// Create status attribute
		ORM::factory("Form_Attribute")
			->set('key', 'status')
			->set("label", "Status")
			->set("type", "varchar")
			->set("input", "select")
			->set("required", false)
			->set("options",
				array(
					"information_sought",
					"is_note_author",
					"believed_alive",
					"believed_missing",
					"believed_dead")
				)
			->set("priority", 6)
			->set('form_id', 1)
			->set('form_group_id', 1)
			->save();
	}

	/**
	 * @BeforeScenario @searchPostFixture
	 */
	public function setupSearchPostFixture()
	{
		// Add posts with searchable data
		ORM::factory("Post")
			->set('form_id', 1)
			->set('title', 'Should be returned when Searching')
			->set('type', 'report')
			->set('content', 'Some description')
			->set('status', 'published')
			->set('id', 99)
			->save();
		ORM::factory("Post")
			->set('form_id', 1)
			->set('title', 'A comment')
			->set('type', 'comment')
			->set('content', 'Some description')
			->set('status', 'published')
			->set('id', 98)
			->save();
		ORM::factory("Post")
			->set('form_id', 1)
			->set('title', 'search by attribute')
			->set('type', 'report')
			->set('content', 'Some description')
			->set('status', 'published')
			->set('id', 97)
			->save();
		ORM::factory("Post_Varchar")
			->set('post_id', 97)
			->set('form_attribute_id', 1)
			->set('value', "special-string")
			->set('id', 50)
			->save();
	}

	/**
	 * @AfterScenario @searchPostFixture
	 */
	public function teardownSearchPostFixture()
	{
		// Remove post
		ORM::factory("Post", 99)->delete();
		ORM::factory("Post", 98)->delete();
		ORM::factory("Post_Varchar", 50)->delete();
		ORM::factory("Post", 97)->delete();
	}

	/** @AfterSuite */
	public static function teardown($event)
	{
		self::_clean_db();
	}

	protected static function _clean_db()
	{
		DB::query(Database::UPDATE, "SET FOREIGN_KEY_CHECKS=0;")->execute();
		// Forms, Attributes, Groups
		DB::query(Database::DELETE, "TRUNCATE TABLE forms")->execute();
		DB::query(Database::DELETE, "TRUNCATE TABLE form_groups")->execute();
		DB::query(Database::DELETE, "TRUNCATE TABLE form_attributes")->execute();
		// Posts & field values
		DB::query(Database::DELETE, "TRUNCATE TABLE posts")->execute();
		DB::query(Database::DELETE, "TRUNCATE TABLE post_datetime")->execute();
		DB::query(Database::DELETE, "TRUNCATE TABLE post_decimal")->execute();
		DB::query(Database::DELETE, "TRUNCATE TABLE post_geometry")->execute();
		DB::query(Database::DELETE, "TRUNCATE TABLE post_int")->execute();
		DB::query(Database::DELETE, "TRUNCATE TABLE post_point")->execute();
		DB::query(Database::DELETE, "TRUNCATE TABLE post_text")->execute();
		DB::query(Database::DELETE, "TRUNCATE TABLE post_varchar")->execute();
		// Tags
		DB::query(Database::DELETE, "TRUNCATE TABLE tags")->execute();
		DB::query(Database::DELETE, "TRUNCATE TABLE posts_tags")->execute();
		// Sets
		DB::query(Database::DELETE, "TRUNCATE TABLE sets")->execute();
		DB::query(Database::DELETE, "TRUNCATE TABLE posts_sets")->execute();
		
		DB::query(Database::UPDATE, "SET FOREIGN_KEY_CHECKS=1;")->execute();
	}
}