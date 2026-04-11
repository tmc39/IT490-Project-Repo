#!/bin/bash
cd ..
#Using the command below to collate files for the bundle is kinda sloppy and probably not how we should do it, but I see no other options for the time being
git diff-tree -r --no-commit-id --name-only HEAD > fileChanges.txt
while read file; do
    zip $1.zip $file
done < fileChanges.txt
zip $1.zip fileChanges.txt
rm fileChanges.txt
mv $1.zip ../Versions
