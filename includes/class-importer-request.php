<?php
namespace Yawave;

class WP_Yawave_Importer_Request extends WP_Async_Request {

	use WP_Yawave_Importer;

	/**
	 * @var string
	 */
	protected $action = 'yawave_import_request';

        /**
	 * Handle
	 *
	 * Override this method to perform any actions required
	 * during the async request.
	 */
	protected function handle() {
            if ($_POST['name'] == "publications") { $this->update_publications (0); }
            if ($_POST['name'] == "categories") { $this->update_categories (); }
            if ($_POST['name'] == "portals") { $this->update_portals (); }
            if ($_POST['name'] == "tags") { $this->update_tags (1); }
            if (strpos($_POST['name'], "tags_") === 0) { $this->update_tags (1); }
            if (strpos($_POST['name'], "categories_") === 0) { $this->update_categories(str_replace("categories_", "", $_POST['name'])); }
            if (strpos($_POST['name'], "publications_") === 0) { $this->update_publcations(str_replace("publications_", "", $_POST['name'])); }
	}

}