#!/bin/bash
cd ..

while read file; do
    if [ "$file" == "rabbitmq" ]; then
       sudo zip -r $1.zip /var/lib/rabbitmq
    else
        zip $1.zip $file
    fi
done < ../Versions/fileChanges.txt

zip $1.zip ../Versions/fileChanges.txt
zip $1.zip ../Versions/commands.txt

mv $1.zip ../Versions
