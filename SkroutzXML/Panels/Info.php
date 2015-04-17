<?php
/**
 * Project: presta-skroutz
 * File: Info.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 10/3/2015
 * Time: 12:21 πμ
 * Since: TODO ${VERSION}
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace SkroutzXML\Panels;


use SkroutzXML\Skroutz;
use XDaRk_v150216\Panels\Panel;

/**
 * Class Info
 * @package SkroutzXML\Panels
 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
 * @since TODO ${VERSION}
 *
 * @property Skroutz $Skroutz
 */
class Info extends Panel{
	protected $tab = 0;
	protected $type = 'sidebar';
	protected $title = 'Info';
	protected $image = false; // TODO set a default image
	protected $input = array();
	protected $submit = array();

	public function __construct( $moduleInstance ) {
		parent::__construct( $moduleInstance );

		$this->addHtml($this->infoContent());
	}

	protected function infoContent(){
		ob_start();
		?>
		<ul class="list-group">
			<?php
		foreach ( $this->XML->getFileInfo() as $k => $v ) {
			?>
			<li class="list-group-item"><?php echo $v['label'] . ': <strong>' . $v['value'] . '</strong>'; ?></li>
			<?php
		}
		?>
		</ul>
		<button id="gen-xml-now" class="btn btn-large btn-primary col-md-12"><?php echo $this->l('Generate XML Now'); ?></button>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('#gen-xml-now').click(function(e){
					e.preventDefault();
					$.get(
						'<?php echo $this->Skroutz->getGenerateURL(); ?>&force_new=1',
						'',
						function(response){
							alert('Done! Products in XML: '+response.firstChild.children[0].childElementCount);
							window.location.reload();
						},
						'xml'
					);
				});
			});
		</script>
		<?php
		return ob_get_clean();
	}
}