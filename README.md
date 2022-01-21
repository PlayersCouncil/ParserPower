Parser Power
============

This repository was forked from the HydraWiki version of Parser Power here: https://gitlab.com/hydrawiki/extensions/ParserPower

In [MediaWiki release 1.35](https://www.mediawiki.org/wiki/Release_notes/1.35), the `Parser::setFunctionTagHook()` function was deprecated, and in [release 1.36](https://www.mediawiki.org/wiki/Release_notes/1.36) the function was entirely removed.  Fandom runs off of 1.33 (currently) and so this change breaks this extension.

The only aspects that this affects are the `<linkpage>`, `<linktext>`, and `<esc>` tags, all of which have been disabled in this fork.

See the [Parser Power Manual](https://help.gamepedia.com/ParserPower_escape_sequences) for detailed instructions on how to use this extension (again, besides the usage of the three `<linkpage>`, `<linktext>`, and `<esc>` tags).