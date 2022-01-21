<?php
/**
 * Simple Class
 *
 * @package   ParserPower
 * @author    Eyes <eyes@aeongarden.com>, Samuel Hilson <shilson@fandom.com>
 * @copyright Copyright � 2013 Eyes
 * @copyright 2019 Wikia Inc.
 * @license   GPL-2.0-or-later
 */

namespace ParserPower;

use PPFrame;
use Title;
use WikiPage;

class ParserPowerSimple {
	/**
	 * Registers the simple, generic parser functions with the parser.
	 *
	 * @param Parser $parser The parser object being initialized.
	 *
	 * @return void
	 */
	public static function setup(&$parser) {
		$parser->setFunctionHook(
			'trim',
			'ParserPower\\ParserPowerSimple::trimRender',
			SFH_OBJECT_ARGS
		);
		$parser->setFunctionHook(
			'uesc',
			'ParserPower\\ParserPowerSimple::uescRender',
			SFH_OBJECT_ARGS
		);
		$parser->setFunctionHook(
			'uescnowiki',
			'ParserPower\\ParserPowerSimple::uescnowikiRender',
			SFH_OBJECT_ARGS
		);
		$parser->setFunctionHook(
			'trimuesc',
			'ParserPower\\ParserPowerSimple::trimuescRender',
			SFH_OBJECT_ARGS
		);
		// $parser->setFunctionTagHook(
		// 	'linkpage',
		// 	'ParserPower\\ParserPowerSimple::linkpageRender',
		// 	0
		// );
		// $parser->setFunctionTagHook(
		// 	'linktext',
		// 	'ParserPower\\ParserPowerSimple::linktextRender',
		// 	0
		// );
		// $parser->setFunctionTagHook(
		// 	'esc',
		// 	'ParserPower\\ParserPowerSimple::escRender',
		// 	0
		// );
		// for ($i = 1; $i < 10; ++$i) {
		// 	$parser->setFunctionTagHook(
		// 		'esc' . $i,
		// 		'ParserPower\\ParserPowerSimple::escRender',
		// 		0
		// 	);
		// }
		$parser->setFunctionHook(
			'ueif',
			'ParserPower\\ParserPowerSimple::ueifRender',
			SFH_OBJECT_ARGS
		);
		$parser->setFunctionHook(
			'or',
			'ParserPower\\ParserPowerSimple::orRender',
			SFH_OBJECT_ARGS
		);
		$parser->setFunctionHook(
			'ueifeq',
			'ParserPower\\ParserPowerSimple::ueifeqRender',
			SFH_OBJECT_ARGS
		);
		$parser->setFunctionHook(
			'token',
			'ParserPower\\ParserPowerSimple::tokenRender',
			SFH_OBJECT_ARGS
		);
		$parser->setFunctionHook(
			'tokenif',
			'ParserPower\\ParserPowerSimple::tokenifRender',
			SFH_OBJECT_ARGS
		);
		$parser->setFunctionHook(
			'ueswitch',
			'ParserPower\\ParserPowerSimple::ueswitchRender',
			SFH_OBJECT_ARGS
		);
		$parser->setFunctionHook(
			'follow',
			'ParserPower\\ParserPowerSimple::followRender',
			SFH_OBJECT_ARGS
		);
	}

	/**
	 * This function performs the trim operation for the trim parser function.
	 *
	 * @param Parser  $parser The parser object. Ignored.
	 * @param PPFrame $frame  The parser frame object.
	 * @param array   $params The parameters and values together, not yet expanded or trimmed.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function trimRender($parser, $frame, $params) {
		return [ isset($params[0]) ? trim($frame->expand($params[0])) : '',
			'noparse' => false
		];
	}

	/**
	 * This function performs the unescape operation for the uesc parser function. This trims the value first, leaving
	 * whitespace intact if it's there after escape sequences are replaced.
	 *
	 * @param Parser  $parser The parser object.
	 * @param PPFrame $frame  The parser frame object.
	 * @param array   $params Attributes values of the tag function.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function uescRender($parser, $frame, $params) {
		return [ isset($params[0]) ? ParserPower::unescape(trim($frame->expand($params[0]))) : '',
			'noparse' => false
		];
	}

	/**
	 * This function performs the unescape operation for the uescnowiki parser function. This trims the value first,
	 * leaving whitespace intact if it's there after escape sequences are replaced. It returns the content wrapped in
	 * <nowiki> tags so that it isn't parsed.
	 *
	 * @param Parser  $parser The parser object.
	 * @param PPFrame $frame  The parser frame object.
	 * @param array   $params Attributes values of the tag function.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function uescnowikiRender($parser, $frame, $params) {
		$text = isset($params[0]) ? ParserPower::unescape(trim($frame->expand($params[0]))) : '';

		return [ '<nowiki>' . $text . '</nowiki>',
			'noparse' => false
		];
	}

	/**
	 * This function performs the unescape operation for the trimuesc parser function. This trims the value after
	 * replacement, so any leading or trailing whitespace is trimmed no matter how it got there.
	 *
	 * @param Parser  $parser The parser object. Ignored.
	 * @param PPFrame $frame  The parser frame object.
	 * @param array   $params The parameters and values together, not yet expanded or trimmed.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function trimuescRender($parser, $frame, $params) {
		return [ isset($params[0]) ? trim(ParserPower::unescape($frame->expand($params[0]))) : '',
			'noparse' => false
		];
	}

	/**
	 * This function performs the delinking operation for the linktext parser function.
	 * This removes internal links from, the given wikicode, replacing them with
	 * the name of the page they would have linked to.
	 *
	 * @param Parser  $parser  The parser object.
	 * @param PPFrame $frame   The parser frame object.
	 * @param string  $text    The text within the tag function.
	 * @param array   $attribs Attributes values of the tag function.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function linkpageRender(&$parser, $frame, $text, $attribs) {
		$text = $parser->replaceVariables($text, $frame);

		if ($text) {
			return [ preg_replace_callback('/\[\[(.*?)\]\]/', 'self::linkpageReplace', $text),
				'noparse' => false
			];
		} else {
			return '';
		}
	}

	/**
	 * This function replaces the links found by linkpageRender and replaces them with the
	 * name of the page they link to.
	 *
	 * @param array $matches The parameters and values together, not yet exploded or trimmed.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function linkpageReplace($matches) {
		$parts = explode('|', $matches[1], 2);
		return $parts[0];
	}

	/**
	 * This function performs the delinking operation for the linktext parser function.
	 * This removes internal links from, the given wikicode, replacing them with
	 * the text that any links would return.
	 *
	 * @param Parser  $parser  The parser object. Ignored.
	 * @param PPFrame $frame   The parser frame object.
	 * @param string  $text    The parameters and values together, not yet exploded or trimmed.
	 * @param array   $attribs
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function linktextRender(&$parser, $frame, $text, $attribs) {
		$text = $parser->replaceVariables($text, $frame);

		if ($text) {
			return [ preg_replace_callback('/\[\[(.*?)\]\]/', 'self::linktextReplace', $text),
				'noparse' => false
			];
		} else {
			return '';
		}
	}

	/**
	 * This function replaces the links found by linktextRender and replaces them with their appropriate link text.
	 *
	 * @param array $matches The parameters and values together, not yet exploded or trimmed.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function linktextReplace($matches) {
		$parts = explode('|', $matches[1], 2);
		if (count($parts) == 2) {
			return $parts[1];
		} else {
			return $parts[0];
		}
	}

	/**
	 * This function escapes all appropriate characters in the given text and returns the result.
	 *
	 * @param Parser  $parser  The parser object.
	 * @param PPFrame $frame   The parser frame object.
	 * @param string  $text    The text within the tag function.
	 * @param array   $attribs Attributes values of the tag function.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function escRender(&$parser, $frame, $text, $attribs) {
		$text = ParserPower::escape($text);

		$text = $parser->replaceVariables($text, $frame);

		return [$text, 'noparse' => false];
	}

	/**
	 * This function performs the test for the ueif function.
	 *
	 * @param Parser  $parser The parser object.
	 * @param PPFrame $frame  The parser frame object.
	 * @param array   $params The parameters and values together, not yet expanded or trimmed.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function ueifRender($parser, $frame, $params) {
		$condition = isset($params[0]) ? trim($frame->expand($params[0])) : '';
		$trueValue = isset($params[1]) ? $params[1] : '';
		$falseValue = isset($params[2]) ? $params[2] : '';

		if ($condition !== '') {
			return [ParserPower::unescape($frame->expand($trueValue)), 'noparse' => false];
		} else {
			return [ParserPower::unescape($frame->expand($falseValue)), 'noparse' => false];
		}
	}

	/**
	 * This function performs the test for the or function.
	 *
	 * @param Parser  $parser The parser object.
	 * @param PPFrame $frame  The parser frame object.
	 * @param array   $params The parameters and values together, not yet expanded or trimmed.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function orRender($parser, $frame, $params) {
		foreach ($params as $param) {
			$inValue = trim($frame->expand($param));

			if ($inValue !== '') {
				return [ParserPower::unescape($inValue), 'noparse' => false];
			}
		}

		return ['', 'noparse' => false];
	}

	/**
	 * This function performs the test for the ueifeq function.
	 *
	 * @param Parser  $parser The parser object.
	 * @param PPFrame $frame  The parser frame object.
	 * @param array   $params The parameters and values together, not yet expanded or trimmed.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function ueifeqRender($parser, $frame, $params) {
		$leftValue = isset($params[0]) ? ParserPower::unescape(trim($frame->expand($params[0]))) : '';
		$rightValue = isset($params[1]) ? ParserPower::unescape(trim($frame->expand($params[1]))) : '';
		$trueValue = isset($params[2]) ? $params[2] : '';
		$falseValue = isset($params[3]) ? $params[3] : '';

		if ($leftValue === $rightValue) {
			return [ParserPower::unescape($frame->expand($trueValue)), 'noparse' => false];
		} else {
			return [ParserPower::unescape($frame->expand($falseValue)), 'noparse' => false];
		}
	}

	/**
	 * This function performs the replacement for the token function.
	 *
	 * @param Parser  $parser The parser object.
	 * @param PPFrame $frame  The parser frame object.
	 * @param array   $params The parameters and values together, not yet expanded or trimmed.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function tokenRender($parser, $frame, $params) {
		$inValue = isset($params[0]) ? trim($frame->expand($params[0])) : '';

		$token = isset($params[1]) ?
			ParserPower::unescape(trim($frame->expand($params[1], PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES))) : 'x';
		$pattern = isset($params[2]) ? $params[2] : 'x';

		return [ParserPower::applyPattern($parser, $frame, $inValue, $token, $pattern), 'noparse' => false];
	}

	/**
	 * This function performs the replacement for the tokenif function.
	 *
	 * @param Parser  $parser The parser object.
	 * @param PPFrame $frame  The parser frame object.
	 * @param array   $params The parameters and values together, not yet expanded or trimmed.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function tokenifRender($parser, $frame, $params) {
		$inValue = isset($params[0]) ? trim($frame->expand($params[0])) : '';
		$default = isset($params[3]) ? trim($frame->expand($params[3])) : '';

		if ($inValue !== '') {
			$token = isset($params[1]) ?
				ParserPower::unescape(trim($frame->expand($params[1], PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES))) :
				'x';
			$pattern = isset($params[2]) ? $params[2] : 'x';

			return [ParserPower::applyPattern($parser, $frame, $inValue, $token, $pattern), 'noparse' => false];
		} else {
			return [ParserPower::unescape($default), 'noparse' => false];
		}
	}

	/**
	 * This function performs the test for the ueswitch function.
	 *
	 * @param Parser  $parser The parser object.
	 * @param PPFrame $frame  The parser frame object.
	 * @param array   $params The parameters and values together, not yet expanded or trimmed.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function ueswitchRender($parser, $frame, $params) {
		$switchKey = isset($params[0]) ? ParserPower::unescape(trim($frame->expand(array_shift($params)))) : '';
		if (count($params) > 0) {
			$default = '';
			if (strpos($frame->expand($params[count($params) - 1]), '=') === false) {
				$default = array_pop($params);
			}

			$keyFound = false;
			foreach ($params as $param) {
				$pair = explode('=', trim($frame->expand($param)), 2);
				if (!$keyFound && ParserPower::unescape($pair[0]) === $switchKey) {
					$keyFound = true;
				}
				if ($keyFound && count($pair) > 1) {
					return [ParserPower::unescape(trim($frame->expand($pair[1]))), 'noparse' => false];
				}
			}
			return [ParserPower::unescape(trim($frame->expand($default))), 'noparse' => false];
		} else {
			return ['', 'noparse' => false];
		}
	}

	/**
	 * This function performs the follow operation for the follow parser function.
	 *
	 * @param Parser  $parser The parser object. Ignored.
	 * @param PPFrame $frame  The parser frame object.
	 * @param array   $params The parameters and values together, not yet expanded or trimmed.
	 *
	 * @return array The function output along with relevant parser options.
	 */
	public static function followRender($parser, $frame, $params) {
		$text = isset($params[0]) ? trim(ParserPower::unescape($frame->expand($params[0]))) : '';

		$output = $text;
		$title = Title::newFromText($text);
		if ($title !== null && $title->getNamespace() !== NS_MEDIA && $title->getNamespace() > -1) {
			$page = WikiPage::factory($title);
			$target = $page->getRedirectTarget();
			if ($target !== null) {
				$output = $target->getPrefixedText();
			}
		}

		return [$output, 'noparse' => false];
	}
}
