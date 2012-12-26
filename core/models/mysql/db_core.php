<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

abstract class DBCore extends DBSuper {
	
	protected function ValidatedInsertUpdate_Process($table, $colSQL, $valueSQL, $updateSQL, $whereSQL, $forceUpdate) {
		$sql = "INSERT INTO " . DB::QuoteIdentifier($table) . " (" . $colSQL . ") VALUES (" . $valueSQL . ")"
			. ($forceUpdate !== 'skipupdate' && strlen($updateSQL) > 0 ? " ON DUPLICATE KEY UPDATE " . $updateSQL : "");
		
		return DB::Update($sql);
	}
}

?>