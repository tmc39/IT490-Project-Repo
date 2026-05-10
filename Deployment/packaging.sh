#!/bin/bash
cd ..

while read file; do
    if [ "$file" == "rabbitmq" ]; then
       sudo zip -r files.zip /var/lib/rabbitmq
    else
        zip files.zip $file
    fi
done < ../Versions/fileChanges.txt
zip $1.zip files.zip
mv $1.zip ../Versions
cd ../Versions
zip $1.zip ./fileChanges.txt
zip $1.zip ./commands.txt



