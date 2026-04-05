#!/bin/bash
cd ..
echo $1
zip -r ${1}.zip backend database Deployment frontend integration public index.php README.md
