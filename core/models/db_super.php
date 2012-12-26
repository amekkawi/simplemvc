<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
*
* LICENSE
* This source file is subject to the MIT license in the file LICENSE.txt.
* The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
*/

abstract class DBSuper {
	
	static function Open() {
		$result = MDB2::connect(Config::Get('dsn'));
		
		// Set charset if configured to.
		if (Config::Exists('dbcharset'))
			$result->setCharset(Config::Get('dbcharset'));
		
		if (MDB2::isError($result)) {
			if (Config::Get('debugqueries') === TRUE) $GLOBALS['DebugInfo'] = $result->getDebugInfo();
			return $result->getMessage();
		}
		
		$GLOBALS['DBCONN'] = $result;
		return true;
	}
	
	static function Close() {
		if (empty($GLOBALS['DBCONN'])) trigger_error('DB must be opened via DB::Open', E_USER_ERROR);
		$GLOBALS['DBCONN']->disconnect();
	}
	
	static function IsOpen() {
		return !empty($GLOBALS['DBCONN']);
	}
	
	static function Query($sql) {
		if (empty($GLOBALS['DBCONN'])) trigger_error('DB must be opened via DB::Open', E_USER_ERROR);
		
		$result =& $GLOBALS['DBCONN']->query($sql);
		
		if (MDB2::isError($result)) {
			if (Config::Get('debugqueries') === TRUE) {
				$GLOBALS['DebugInfo'] = $result->getDebugInfo();
				$GLOBALS['DebugSQL'] = $sql;
			}
			return $result->getMessage();
		}
		
		return $result;
	}
	
	static function &QueryAll($sql) {
		if (empty($GLOBALS['DBCONN'])) trigger_error('DB must be opened via DB::Open', E_USER_ERROR);
		
		$recordset =& $GLOBALS['DBCONN']->queryAll($sql, null, MDB2_FETCHMODE_ASSOC);
		
		if (MDB2::isError($recordset)) {
			if (Config::Get('debugqueries') === TRUE) {
				$GLOBALS['DebugInfo'] = $recordset->getDebugInfo();
				$GLOBALS['DebugSQL'] = $sql;
			}
			$error = $recordset->getMessage();
			return $error;
		}
		
		return $recordset;
	}
	
	static function QueryRow($sql) {
		if (empty($GLOBALS['DBCONN'])) trigger_error('DB must be opened via DB::Open', E_USER_ERROR);
		
		$row =& $GLOBALS['DBCONN']->queryRow($sql, null, MDB2_FETCHMODE_ASSOC);
		
		if (MDB2::isError($row)) {
			if (Config::Get('debugqueries') === TRUE) {
				$GLOBALS['DebugInfo'] = $row->getDebugInfo();
				$GLOBALS['DebugSQL'] = $sql;
			}
			return $row->getMessage();
		}
		
		return $row;
	}
	
	/**
	 * 
	 * @param string $sql
	 * @param int $pagenum The page number to start at (start count at 1)
	 * @param int $perpage The number of records per page
	 * @return array|string
	 */
	static function &QueryPaged($sql, $pagenum, $perpage) {
		if (empty($GLOBALS['DBCONN'])) trigger_error('DB must be opened via DB::Open', E_USER_ERROR);
		
		// Get the recordset for the query.
		$recordset =& DB::Query($sql);
		
		if (is_string($recordset)) {
			return $recordset;
		}
		else {
			$total = DB::RowCount($recordset);
			
			// Change the result to an array.
			$result = array('total' => $total, 'records' => array());
			
			if ($total > 0) {
				// Make sure the page num is at least 1, and not larger than there are records.
				$pagenum = max(1, min($pagenum, ceil($total / $perpage)));
				
				// Return the correct page and page count so they can be used.
				$result['page'] = $pagenum;
				$result['totalpages'] = ceil($total / $perpage);
				
				$from = ($pagenum - 1) * $perpage;
				$to = min($from + ($perpage - 1), $total - 1);
				
				$result['rowfrom'] = $from + 1;
				$result['rowto'] = $to + 1;
				
				// Seek to the record before the first, unless we are the beginning of the recordset.
				if (DB::Seek($recordset, $from > 0 ? $from - 1 : $from) === TRUE) {
					if ($from > 0) {
						$row =& DB::FetchRow($recordset);
						if (MDB2::isError($row)) {
							return $row->getMessage();
						}
						else {
							$result['previous'] = $row;
						}
					}
					
					for ($i = 0; $i <= ($to - $from); $i++) {
						$row =& DB::FetchRow($recordset);
						if (MDB2::isError($row)) {
							return $row->getMessage();
						}
						elseif (!is_array($row)) {
							return "Could not retrieve row (possibly tried to fetch row beyond row count)";
						}
						else {
							$result['records'][count($result['records'])] = $row;
						}
					}
					
					if ($to < $total - 1) {
						$row =& DB::FetchRow($recordset);
						if (MDB2::isError($row)) {
							return $row->getMessage();
						}
						else {
							$result['next'] = $row;
						}
					}
				}
				else {
					return "Could not seek to record";
				}
			}
		}
		
		return $result;
	}
	
	static function Update($sql) {
		if (empty($GLOBALS['DBCONN'])) trigger_error('DB must be opened via DB::Open', E_USER_ERROR);
		
		$affected =& $GLOBALS['DBCONN']->exec($sql);
		
		if (MDB2::isError($affected)) {
			if (Config::Get('debugqueries') === TRUE) {
				$GLOBALS['DebugInfo'] = $affected->getDebugInfo();
				$GLOBALS['DebugSQL'] = $sql;
			}
			return $affected->getMessage();
		}
		
		return $affected;
	}
	
	static function FetchRow(&$recordset) {
		return $recordset->fetchRow(MDB2_FETCHMODE_ASSOC);
	}
	
	static function &FetchAll(&$recordset) {
		$result =& $recordset->fetchAll(MDB2_FETCHMODE_ASSOC);
		return $result;
	}
	
	/**
	 * Returns the actual row number that was last fetched (count from 0).
	 * @param object $recordset
	 * @return int|object MDB2 Error Object or the number of rows
	 */
	static function CurrentRow(&$recordset) {
		return $recordset->rowCount();
	}
	
	/**
	 * Returns the number of rows in a result object.
	 * @param object $recordset
	 * @return int 
	 */
	static function RowCount(&$recordset) {
		return $recordset->numRows();
	}
	
	/**
	 * Seek to a specific row in a result set
	 * @param object $recordset
	 * @param int $rownum The row number to seek to (count from 0).
	 * @return bool|object TRUE on success, a MDB2 error on failure
	 */
	static function Seek(&$recordset, $rownum) {
		return $recordset->seek($rownum);
	}
	
	/**
	 * Free the resources used by the recordset.
	 * @param object $recordset
	 */
	static function Free(&$recordset) {
		$recordset->free();
	}
	
	/**
	 * Quote and escape a value so it can be inserted into SQL.
	 * @param string $value
	 * @param boolean $wildcards [optional] Whether or not to quote wildcard characters, i.e. % and _
	 * @return The escaped string surrounded by quotes.
	 */
	static function Quote($value, $wildcards = false) {
		if ($value === "") return str_replace('x', '', DB::Quote('x'));
		return $GLOBALS['DBCONN']->quote($value, null, true, $wildcards);
	}
	
	/**
	 * Quote and escape a identifier (e.g. table or column name), but only if necessary.
	 * @param string $value
	 * @return The escaped value surrounded by identifier quotes.
	 */
	static function QuoteIdentifier($value) {
		if (preg_match("/^[a-z_\-0-9]+$/i", $value)) {
			return $value;
		}
		else {
			return $GLOBALS['DBCONN']->quoteIdentifier($value);
		}
	}
	
	/**
	 * Escape a value so it can be inserted into SQL.
	 * @param string $value
	 * @param boolean $wildcards [optional] Whether or not to quote wildcard characters, i.e. % and _
	 * @return The escaped string.
	 */
	static function Escape($value, $wildcards = false) {
		return $GLOBALS['DBCONN']->escape($value, $wildcards);
	}
	
	/**
	 * Create a unique ID.
	 * @return string
	 */
	static function GenerateID() {
		$row =& DB::QueryRow("SELECT UUID() id");
		return (is_string($row) ? $row : $row['id'] );
	}
	
	/**
	 * Perform a safe INSERT. Column values are validated against V::Check().
	 * @param string $table The name of the table.
	 * @param array $keys A list of primary keys for the table.
	 * @param array $data The data to be inserted.
	 * @param array $defaults [optional] The default values for the columns.
	 * @return string|int An error string if a column value is invalid. Otherwise, the same values returned from DB::Update.
	 */
	static function ValidatedInsert($table, $data, $defaults = NULL) {
		return DB::ValidatedInsertUpdate($table, array(), $data, $defaults, 'skipupdate');
	}
	
	/**
	 * Perform a safe INSERT ... ON DUPLICATE KEY UPDATE. Column values are validated against V::Check().
	 * @param string $table The name of the table.
	 * @param array $keys A list of primary keys for the table.
	 * @param array $data The data to be inserted.
	 * @param array $defaults [optional] The default values for the columns.
	 * @param bool|string $forceUpdate [optional] Set to TRUE to update the keys as well so that no error messages are returned.
	 * @return string|int An error string if a column value is invalid. Otherwise, the same values returned from DB::Update.
	 */
	static function ValidatedInsertUpdate($table, $keys, $data, $defaults = NULL, $forceUpdate = false) {
		
		$keys = array_flip($keys);
		$merged = is_array($defaults) ? array_merge($defaults, $data) : $data;
		$cols = array_keys(is_array($defaults) ? $defaults : $data);
		
		// Check that $data contains values for each of the $keys.
		foreach ($keys as $key => $value) {
			if (!array_key_exists($key, $data)) {
				return "Value for key '".$key."' was not passed.";
			}
		}
		
		$colSQL = "";
		$valueSQL = "";
		$updateSQL = ""; //$forceUpdate === true ? DB::QuoteIdentifier($cols[0]) . " = " . DB::QuoteIdentifier($cols[0]) : "";
		$whereSQL = "";
		
		foreach ($cols as $col) {
			// Validate the passsed arguments.
			if (isset($data[$col]) && !V::Check('table:' . $table . ':' . $col, $data[$col]))
				return "Invalid value for '".$table.'.'.$col."'.";
			
			// Build the column and VALUES SQL
			$colSQL .= (strlen($colSQL) > 0 ? ", " : "") . DB::QuoteIdentifier($col);
			$valueSQL .= (strlen($valueSQL) > 0 ? ", " : "") . DB::Quote($merged[$col]);
			
			// Build the UPDATE SQL
			if (isset($data[$col]) && ($forceUpdate === TRUE || !array_key_exists($col, $keys))) {
				$updateSQL .= (strlen($updateSQL) > 0 ? ", " : "") . DB::QuoteIdentifier($col) . " = " . DB::Quote($merged[$col]);
			}
		}
		
		return DB::ValidatedInsertUpdate_Process($table, $colSQL, $valueSQL, $updateSQL, $whereSQL, $forceUpdate);
		
		$sql = "INSERT INTO " . DB::QuoteIdentifier($table) . " (" . $colSQL . ") VALUES (" . $valueSQL . ")"
			. ($forceUpdate !== 'skipupdate' && strlen($updateSQL) > 0 ? " ON DUPLICATE KEY UPDATE " . $updateSQL : "");
		
		return DB::Update($sql);
	}
	
	abstract protected function ValidatedInsertUpdate_Process($table, $colSQL, $valueSQL, $updateSQL, $whereSQL, $forceUpdate);
	
	/**
	 * 
	 * @param string $table The name of the table.
	 * @param array $keys A list of primary keys for the table.
	 * @param array $data The data to be updated.
	 * @return string|int An error string if a column value is invalid. Otherwise, the same values returned from DB::Update.
	 */
	static function ValidatedUpdate($table, $keys, $data) {
		$keys = array_flip($keys);
		
		$setSQL = "";
		$whereSQL = "";
		
		foreach ($data as $key => $value) {
			// Validate the column name.
			if (!V::Check('table:' . $table, $key))
				return "Invalid column for '".$table."'.";
			
			// Validate the column's value.
			if (!V::Check('table:' . $table . ':' . $key, $value))
				return "Invalid value for '".$table.'.'.$key."'.";
			
			// Include the data in the WHERE if it is a key.
			if (array_key_exists($key, $keys)) {
				$whereSQL .= ($whereSQL != "" ? ' AND ' : '') .  DB::QuoteIdentifier($key) . ' = ' . DB::Quote($value);
			}
			
			// Otherwise, it is a column to be updated.
			else {
				$setSQL .= ($setSQL != "" ? ', ' : '') .  DB::QuoteIdentifier($key) . ' = ' . DB::Quote($value);
			}
		}
		
		$sql = "UPDATE " . DB::QuoteIdentifier($table) . " SET " . $setSQL . " WHERE (" . $whereSQL . ")";
		
		return DB::Update($sql);
	}
	
	/**
	 * Get the current date/time in a format the DB wants.
	 * @return 
	 */
	static function Now($diff = 0) {
		return date('Y-m-d H:i:s', time() + $diff);
	}
	
	static function FormatTimestamp($timestamp) {
		return date('Y-m-d H:i:s', $timestamp);
	}
	
	static function ResultsTable($sql) {
		$results =& DB::Query($sql);
		if (is_string($results)) {
			echo '<table class="ResultsTable"><tr><th>SQL:</th><td>' . HTML::Encode($sql) . "</td></tr><tr><th>DebugInfo:</th><td>". HTML::Pre($GLOBALS['DebugInfo']) ."</td></tr></table>";
		}
		else {
			echo '<table class="ResultsTable">';
			$row = DB::FetchRow($results);
			
			if (DB::RowCount($results) == 0) {
				echo '<tr><td>'.HTML::Encode($sql)."</td></tr>";
				echo '<tr><td>No Records</td></tr>';
			}
			else {
				$headers = array_keys($row);
				
				echo '<tr><td colspan="'.count($headers).'">'.HTML::Encode($sql)."</td></tr>";
				
				echo "<tr>";
				for ($i = 0; $i < count($headers); $i++) {
					echo "<th>".HTML::Encode($headers[$i])."</th>";
				}
				echo "</tr>";
				
				while (!is_null($row)) {
					
					echo "<tr>";
					for ($i = 0; $i < count($headers); $i++) {
						if (is_null($row[$headers[$i]])) {
							echo '<td class="ResultsTable-NULL">NULL</td>';
						}
						else {
							echo "<td>".HTML::Encode($row[$headers[$i]])."</td>";
						}
					}
					echo "</tr>";
					
					$row = DB::FetchRow($results);
				}
			}
				
			echo "</table>";
		}
	}
}

?>