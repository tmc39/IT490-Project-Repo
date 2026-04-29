#!/bin/bash
cd ..
$1 > version.txt

mv ./version.txt /home/message-broker/Deployment-Server/Versions

while read file; do
    if [ "$file" == "rabbitmq" ]; then
       sudo zip -r $1.zip /var/lib/rabbitmq
    else
        zip $1.zip $file
    fi
done < $2/fileChanges.txt
zip $1.zip $2/fileChanges.txt
zip $1.zip $2/commands.txt


mv $1.zip ../Versions
