#!/bin/bash

sed -i "s#{'\([a-z]*\)'}#['\1']#g" ./include/database/PearDatabase.php
sed -i "s#\$\([a-z][a-z]*\){\$\([a-z][a-z]*\)}#$\1[$\2]#g" ./include/database/PearDatabase.php ./libraries/htmlpurifier/library/HTMLPurifier/Encoder.php
sed -i "s#matchAny(\$input)#matchAny(\$input=null)#g" ./libraries/antlr/BaseRecognizer.php
sed -i "s#matchAny()#matchAny(\$input=null)#g" ./libraries/antlr/AntlrLexer.php
sed -i "s#function recover(\$re)#function recover(\$input,\$re=null)#g" ./libraries/antlr/AntlrLexer.php
sed -i "s#traceIn(\$ruleName, \$ruleIndex)#traceIn(\$ruleName, \$ruleIndex, \$inputSymbol=null)#g" ./libraries/antlr/AntlrLexer.php ./libraries/antlr/AntlrParser.php
sed -i "s#traceOut(\$ruleName, \$ruleIndex)#traceOut(\$ruleName, \$ruleIndex, \$inputSymbol=null)#g" ./libraries/antlr/AntlrLexer.php ./libraries/antlr/AntlrParser.php
sed -i "s#get_magic_quotes_gpc()#true#g" ./includes/http/Request.php
sed -i "s#function __autoload(\$class)#function __autoload2(\$class)#g" ./libraries/htmlpurifier/library/HTMLPurifier.autoload.php
sed -i "s#{0}#[0]#g" ./libraries/htmlpurifier/library/HTMLPurifier/TagTransform/Font.php ./vtlib/thirdparty/network/Request.php ./vtlib/thirdparty/network/Net/URL.php
sed -i "s#include_once 'config.php';#error_reporting(E_ALL\&~E_WARNING\&~E_DEPRECATED); include_once 'polyfill.php'; include_once 'config.php';#g" ./index.php
sed -i "s#function Install_ConfigFileUtils_Model#function __construct#g" ./modules/Install/models/ConfigFileUtils.php
sed -i "s#function DefaultDataPopulator#function __construct#g" ./modules/Users/DefaultDataPopulator.php
sed -i "s#function Vtiger_PackageUpdate#function __construct#g" ./vtlib/Vtiger/PackageUpdate.php
sed -i "s#function Vtiger_PackageImport#function __construct#g" ./vtlib/Vtiger/PackageImport.php
sed -i "s#function Vtiger_PackageExport#function __construct#g" ./vtlib/Vtiger/PackageExport.php
sed -i "s#csrf_check_token(\$token) {#csrf_check_token(\$token) { return true;#g" ./libraries/csrf-magic/csrf-magic.php
sed -i "s#count(\$params) > 0#is_array(\$params) \&\& count(\$params) > 0#g" ./include/database/PearDatabase.php
sed -i "s#+ rand(1,9999999) +#. rand(1,9999999) .#g" ./modules/Install/models/ConfigFileUtils.php
