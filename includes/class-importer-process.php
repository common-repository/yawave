<?php
namespace Yawave;

class WP_Yawave_Importer_Process {

	use WP_Yawave_Importer;
        use WP_Yawave_Categories_Importer;
        use WP_Yawave_Publications_Importer;
        use WP_Yawave_Portals_Importer;
		use WP_Yawave_Tags_Importer;
		use WP_Yawave_Liveblog_Importer;

	/**
	 * @var string
	 */
	protected $action = 'yawave_import_process';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
            

            
                if (!function_exists('wp_insert_category')) { 
                    return $item; 
                }
                

                
                $this->set_api_token_and_app_id();
                $this->log("#### WP_Yawave_Importer_Process :: task :: ". $item . " is running...");
                if ($item == "categories") { $this->update_categories (); }
                if (strpos($item, "categories_") === 0) { 
                    $this->update_categories((int)str_replace("categories_", "", $item)); 
                }
                if ($item == "portals") { $this->update_portals (); }
                if ($item == "tags") { $this->update_tags (1); }
                if (strpos($item, "tags_") === 0) { 
                    $this->update_tags ((int)str_replace("tags_", "", $item)); 
                }
                if ($item == "publications") { $this->update_publications (0); }
                if (strpos($item, "publications_") === 0) { 
                    $this->update_publications ((int)str_replace("publications_", "", $item)); 
                }
		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		//parent::complete();

		// Show notice to user or perform some other arbitrary task...
	}

}
