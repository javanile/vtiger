#!/usr/bin/env bash

source contrib/versions.sh

version=$1
version_info=${versions[$version]}
version_dir=$(echo "${versions[$version]}" | cut -d, -f1)

echo "Info: $version_dir"

exit


[[ -d "$version" ]] || mkdir ${version}
#rm -fr ${version}/* && true

template=Dockerfile.$(echo ${versions[$version]} | cut -d, -f1).template
php_version=$(echo ${versions[$version]} | cut -d, -f2)
database_package=$(echo ${versions[$version]} | cut -d, -f3)
hosting=$(echo ${versions[$version]} | cut -d, -f4)
directory=$(echo ${versions[$version]} | cut -d, -f5)
download=${source_code_hosting[$hosting]}$(echo ${versions[$version]} | cut -d, -f6)

sed -e 's!%%VT_VERSION%%!'"${version}"'!' \
    -e 's!%%VT_DOWNLOAD%%!'"${download}"'!' \
    -e 's!%%VT_DIRECTORY%%!'"${directory}"'!' \
    -e 's!%%DATABASE_PACKAGE%%!'"${database_package}"'!' \
    -e 's!%%PHP_VERSION%%!'"${php_version}"'!' \
    -r ${template} > ${version}/Dockerfile

if [[ "${version}" == "${develop_version}" ]]; then
    sed -e ':a;N;$!ba;s!\#\${DEVELOP_LAYER}\n!!g' -ri ${version}/Dockerfile
    sed -e ':a;N;$!ba;s!\#\${RELEASE_LAYER}\n\#!## RELEASE LAYER: !g' -ri ${version}/Dockerfile
else
    sed -e ':a;N;$!ba;s!\#\${RELEASE_LAYER}\n\#!!g' -ri ${version}/Dockerfile
    sed -e ':a;N;$!ba;s!\#\${DEVELOP_LAYER}\n!## DEVELOP LAYER: !g' -ri ${version}/Dockerfile
    sed -e ':a;N;$!ba;s!ENV LAYER_BREAK=true\n!!g' -ri ${version}/Dockerfile
    sed -e ':a;N;$!ba;s!\${LAYER_BREAK}\nRUN!\\\n   !g' -ri ${version}/Dockerfile
fi

sed /^##/d -i ${version}/Dockerfile

for file in "${files[@]}"; do
    [[ -f "$file" ]] || continue
    cat ${file} > ${version}/${file}
done

chmod +x ${version}/docker-vtiger-*.sh
chmod 600 ${version}/crontab
