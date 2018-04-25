#!/bin/bash
#
SCRIPT_PATH=`dirname $0`
cd $SCRIPT_PATH
if [ -d "../.git" ]; then
	if ! [ -L '../.git/hooks' ]; then
		echo 'create link `../.git/hooks`'
		rm -rf ../.git/hooks
		ln -s ../docs/hooks ../.git/hooks
		chmod +x hooks/pre-commit
	else
		echo 'link `../.git/hooks` is exist'
	fi
else
	echo 'not exist folder `../.git`'
fi
echo 'init complite'
cd -