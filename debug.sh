#!/usr/bin/env bash
set -e

## Settings
debug_file=.debugfile
target_dir=/var/www/html
watch_dir=/app/.debug

## Create watch_dir
if [[ ! -d ${watch_dir} ]]; then
    mkdir ${watch_dir}
    chmod 777 ${watch_dir}
fi

## Create debug_file
if [[ ! -f ${watch_dir}/${debug_file} ]]; then
    touch ${watch_dir}/${debug_file}
    chmod 777 ${watch_dir}/${debug_file}
fi

process_debug_file () {
    while IFS= read file || [[ -n "${file}" ]]; do
        [[ -z "${file}" ]] && continue
        [[ "${file::1}" == "#" ]] && continue
        [[ -f ${watch_dir}/${file} ]] && continue
        echo "+ ${file}"
        if [[ ! -f ${target_dir}/${file} ]]; then
            mkdir -p $(dirname ${target_dir}/${file}) && true
            touch ${target_dir}/${file}
            chmod 777 -R $(dirname ${target_dir}/${file})
        fi
        mkdir -p $(dirname ${watch_dir}/${file}) && true
        cp ${target_dir}/${file} ${watch_dir}/${file}
        chmod 777 -R ${watch_dir}
    done < ${watch_dir}/${debug_file}
}

## Files watcher
echo "Add your file names on '.debug/.debugfile'"
echo "Watching for debug... (Stop with [Ctrl+C])"
process_debug_file
inotifywait -q -r -e moved_to,create -m ${watch_dir} |
while read -r directory events current_file; do
    #echo "${events} ${directory} ${current_file}"
    if [[ "${current_file}" = "${debug_file}" ]]; then
        process_debug_file
    else
        while IFS= read file || [[ -n "${file}" ]]; do
            [[ -z "${file}" ]] && continue
            [[ "${file::1}" == "#" ]] && continue
            if [[ "${directory}${current_file}" = "${watch_dir}/${file}" ]]; then
                echo "> ${file}"
                cp ${watch_dir}/${file} ${target_dir}/${file}
            fi
        done < ${watch_dir}/${debug_file}
    fi
    #echo ">>> ${filename}"
done
