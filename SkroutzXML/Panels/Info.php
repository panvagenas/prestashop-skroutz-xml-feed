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


use XDaRk_v150216\Panels\Panel;

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
			<li class="list-group-item"><?php echo $k . ': <strong>' . $v . '</strong>'; ?></li>
			<?php
		}
		?>
		</ul>
		<?php
		return ob_get_clean();
	}
}