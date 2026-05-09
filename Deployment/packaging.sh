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
done < /home/message-broker/Deployment-Server/Sensitive-Info/fileChanges.txt
zip $1.zip /home/message-broker/Deployment-Server/Versions/fileChanges.txt
zip $1.zip /home/message-broker/Deployment-Server/Versions/version.txt

mv $1.zip ../Versions
