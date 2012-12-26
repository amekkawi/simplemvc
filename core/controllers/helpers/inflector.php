<?php
/**
 * Pluralize and singularize English words.
 * 
 * Based on CakePHP's inflector. Copyrights for the original:
 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * 
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 */

class Inflector {
	
	private static $_initialized = false;
	
	// Cache of perviously pluralized or singularized words.
	private static $_pluralizedCache = array();
	private static $_singularizedCache = array();
	
	
	// Rules are Regular Expression replacements.
	
	private static $_pluralRules = array(
		'/(s)tatus$/i' => '\1\2tatuses',
		'/(quiz)$/i' => '\1zes',
		'/^(ox)$/i' => '\1\2en',
		'/([m|l])ouse$/i' => '\1ice',
		'/(matr|vert|ind)(ix|ex)$/i'  => '\1ices',
		'/(x|ch|ss|sh)$/i' => '\1es',
		'/([^aeiouy]|qu)y$/i' => '\1ies',
		'/(hive)$/i' => '\1s',
		'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
		'/sis$/i' => 'ses',
		'/([ti])um$/i' => '\1a',
		'/(p)erson$/i' => '\1eople',
		'/(m)an$/i' => '\1en',
		'/(c)hild$/i' => '\1hildren',
		'/(buffal|tomat)o$/i' => '\1\2oes',
		'/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
		'/us$/' => 'uses',
		'/(alias)$/i' => '\1es',
		'/(ax|cris|test)is$/i' => '\1es',
		'/s$/' => 's',
		'/^$/' => '',
		'/$/' => 's'
	);
	
	private static $_singularRules = array(
		'/(s)tatuses$/i' => '\1\2tatus',
		'/^(.*)(menu)s$/i' => '\1\2',
		'/(quiz)zes$/i' => '\\1',
		'/(matr)ices$/i' => '\1ix',
		'/(vert|ind)ices$/i' => '\1ex',
		'/^(ox)en/i' => '\1',
		'/(alias)(es)*$/i' => '\1',
		'/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
		'/([ftw]ax)es/' => '\1',
		'/(cris|ax|test)es$/i' => '\1is',
		'/(shoe)s$/i' => '\1',
		'/(o)es$/i' => '\1',
		'/ouses$/' => 'ouse',
		'/uses$/' => 'us',
		'/([m|l])ice$/i' => '\1ouse',
		'/(x|ch|ss|sh)es$/i' => '\1',
		'/(m)ovies$/i' => '\1\2ovie',
		'/(s)eries$/i' => '\1\2eries',
		'/([^aeiouy]|qu)ies$/i' => '\1y',
		'/([lr])ves$/i' => '\1f',
		'/(tive)s$/i' => '\1',
		'/(hive)s$/i' => '\1',
		'/(drive)s$/i' => '\1',
		'/([^fo])ves$/i' => '\1fe',
		'/(^analy)ses$/i' => '\1sis',
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
		'/([ti])a$/i' => '\1um',
		'/(p)eople$/i' => '\1\2erson',
		'/(m)en$/i' => '\1an',
		'/(c)hildren$/i' => '\1\2hild',
		'/(n)ews$/i' => '\1\2ews',
		'/^(.*us)$/' => '\\1',
		'/s$/i' => ''
	);
	
	
	// Uninflected words are regular expressions of Words that do not change.
	
	private static $_uninflectedPlural = array(
		'.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', 'Amoyese',
		'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus', 'carp', 'chassis', 'clippers',
		'cod', 'coitus', 'Congoese', 'contretemps', 'corps', 'debris', 'diabetes', 'djinn', 'eland', 'elk',
		'equipment', 'Faroese', 'flounder', 'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
		'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings', 'jackanapes', 'Kiplingese',
		'Kongoese', 'Lucchese', 'mackerel', 'Maltese', 'media', 'mews', 'moose', 'mumps', 'Nankingese', 'news',
		'nexus', 'Niasese', 'Pekingese', 'People', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese', 'proceedings',
		'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors', 'sea[- ]bass', 'series', 'Shavese', 'shears',
		'siemens', 'species', 'swine', 'testes', 'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese',
		'whiting', 'wildebeest', 'Yengeese'
	);
	
	private static $_uninflectedSingular = array(
		'.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', '.*ss', 'Amoyese',
		'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus', 'carp', 'chassis', 'clippers',
		'cod', 'coitus', 'Congoese', 'contretemps', 'corps', 'debris', 'diabetes', 'djinn', 'eland', 'elk',
		'equipment', 'Faroese', 'flounder', 'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
		'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings', 'jackanapes', 'Kiplingese',
		'Kongoese', 'Lucchese', 'mackerel', 'Maltese', 'media', 'mews', 'moose', 'mumps', 'Nankingese', 'news',
		'nexus', 'Niasese', 'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese', 'proceedings',
		'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors', 'sea[- ]bass', 'series', 'Shavese', 'shears',
		'siemens', 'species', 'swine', 'testes', 'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese',
		'whiting', 'wildebeest', 'Yengeese'
	);
	
	
	// Irregular words that do not follow the above rules.
	
	private static $_irregularPlural = array(
		'atlas' => 'atlases',
		'beef' => 'beefs',
		'brother' => 'brothers',
		'child' => 'children',
		'corpus' => 'corpuses',
		'cow' => 'cows',
		'ganglion' => 'ganglions',
		'genie' => 'genies',
		'genus' => 'genera',
		'graffito' => 'graffiti',
		'hoof' => 'hoofs',
		'loaf' => 'loaves',
		'man' => 'men',
		'money' => 'monies',
		'mongoose' => 'mongooses',
		'move' => 'moves',
		'mythos' => 'mythoi',
		'numen' => 'numina',
		'occiput' => 'occiputs',
		'octopus' => 'octopuses',
		'opus' => 'opuses',
		'ox' => 'oxen',
		'penis' => 'penises',
		'person' => 'people',
		'sex' => 'sexes',
		'soliloquy' => 'soliloquies',
		'testis' => 'testes',
		'trilby' => 'trilbys',
		'turf' => 'turfs');
	
	private static $_irregularSingular = array(
		'atlases' => 'atlas',
		'beefs' => 'beef',
		'brothers' => 'brother',
		'children' => 'child',
		'corpuses' => 'corpus',
		'cows' => 'cow',
		'ganglions' => 'ganglion',
		'genies' => 'genie',
		'genera' => 'genus',
		'graffiti' => 'graffito',
		'hoofs' => 'hoof',
		'loaves' => 'loaf',
		'men' => 'man',
		'monies' => 'money',
		'mongooses' => 'mongoose',
		'moves' => 'move',
		'mythoi' => 'mythos',
		'numina' => 'numen',
		'occiputs' => 'occiput',
		'octopuses' => 'octopus',
		'opuses' => 'opus',
		'oxen' => 'ox',
		'penises' => 'penis',
		'people' => 'person',
		'sexes' => 'sex',
		'soliloquies' => 'soliloquy',
		'testes' => 'testis',
		'trilbys' => 'trilby',
		'turfs' => 'turf',
		'waves' => 'wave'
	);
	
	// A single RegEx form of the respective array.
	private static $_uninflectedPluralRegEx = null;
	private static $_uninflectedSingularRegEx = null;
	private static $_irregularPluralRegEx = null;
	private static $_irregularSingularRegEx = null;
	
	private static function Initialize() {
		if (Config::Exists('inflection_plural_rules'))
			self::$_pluralRules = array_merge(self::$_pluralRules, Config::Exists('inflection_plural_rules'));

		if (Config::Exists('inflection_singular_rules'))
			self::$_singularRules = array_merge(self::$_singularRules, Config::Exists('inflection_singular_rules'));
		
		if (Config::Exists('inflection_uninflected_plural'))
			self::$_uninflectedPlural = array_merge(self::$_uninflectedPlural, Config::Exists('inflection_uninflected_plural'));
		
		if (Config::Exists('inflection_uninflected_singular'))
			self::$_irregularSingular = array_merge(self::$_irregularSingular, Config::Exists('inflection_uninflected_singular'));
		
		if (Config::Exists('inflection_irregular_plural'))
			self::$_irregularPlural = array_merge(self::$_irregularPlural, Config::Exists('inflection_irregular_plural'));
		
		if (Config::Exists('inflection_irregular_singular'))
			self::$_irregularSingular = array_merge(self::$_irregularSingular, Config::Exists('inflection_irregular_singular'));
		
		self::$_uninflectedPluralRegEx = '(?:' . join('|', self::$_uninflectedPlural) . ')';
		self::$_uninflectedSingularRegEx = '(?:' . join('|', self::$_uninflectedSingular) . ')';
		self::$_irregularPluralRegEx = '(?:' . join('|', array_keys(self::$_irregularPlural)) . ')';
		self::$_irregularSingularRegEx = '(?:' . join('|', array_keys(self::$_irregularSingular)) . ')';
		
		self::$_initialized = true;
	}
	
	/**
	 * Return $word in plural form.
	 *
	 * @param string $word Word in singular
	 * @return string Word in plural
	 * @access public
	 * @static
	 */
	public static function Pluralize($word) {
		
		if (!self::$_initialized) self::Initialize();
		
		// Return from the cache is the word has already been pluralized.
		if (isset(self::$_pluralizedCache[$word])) {
			return self::$_pluralizedCache[$word];
		}
		
		// Check the list of uninflected words.
		if (preg_match('/^(' . self::$_uninflectedPluralRegEx . ')$/i', $word, $regs)) {
			self::$_pluralizedCache[$word] = $word;
			return $word;
		}
		
		// Check the list of irregular words.
		if (preg_match('/(.*)\\b(' . self::$_irregularPluralRegEx . ')$/i', $word, $regs)) {
			self::$_pluralizedCache[$word] = $regs[1] . substr($word, 0, 1) . substr(self::$_irregularPlural[strtolower($regs[2])], 1);
			return self::$_pluralizedCache[$word];
		}
		
		// Check each of the rules.
		foreach (self::$_pluralRules as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				self::$_pluralizedCache[$word] = preg_replace($rule, $replacement, $word);
				return self::$_pluralizedCache[$word];
			}
		}
		
		// Did not match any rules for pluralization.
		return null;
	}

	/**
	 * Return $word in singular form.
	 *
	 * @param string $word Word in plural
	 * @return string Word in singular
	 * @access public
	 * @static
	 */
	public static function Singularize($word) {
		
		if (!self::$_initialized) self::Initialize();
		
		// Return from the cache is the word has already been singularized.
		if (isset(self::$_singularizedCache[$word])) {
			return self::$_singularizedCache[$word];
		}
		
		// Check the list of uninflected words.
		if (preg_match('/^(' . self::$_uninflectedSingularRegEx . ')$/i', $word, $regs)) {
			self::$_singularizedCache[$word] = $word;
			return $word;
		}
		
		// Check the list of irregular words.
		if (preg_match('/(.*)\\b(' . self::$_irregularSingularRegEx . ')$/i', $word, $regs)) {
			self::$_singularizedCache[$word] = $regs[1] . substr($word, 0, 1) . substr(self::$_irregularSingular[strtolower($regs[2])], 1);
			return self::$_singularizedCache[$word];
		}
		
		// Check each of the rules.
		foreach (self::$_singularRules as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				self::$_singularizedCache[$word] = preg_replace($rule, $replacement, $word);
				return self::$_singularizedCache[$word];
			}
		}
		
		// Did not match any rules for singularization.
		self::$_singularizedCache[$word] = $word;
		return $word;
	}
}
?>