#!/usr/bin/env bash

# exit program when send SIGTERM
trap "trap - SIGTERM && kill -- -$$" SIGINT SIGTERM EXIT

# main function
run() {
  # exec server.php as a background process
  php app/server.php &
  pid=$!
}

# start when exec first

# detect file change by fswatch (just UPDATE event only)
# exclude server.sh and vendor/ directory
fswatch --exclude="$0" --exclude"vendor/" --one-per-batch --event=Updated . | while read line; do

    if [[ -n $pid ]]; then
        kill $pid
    fi
    
    run
done
