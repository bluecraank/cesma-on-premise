#! /bin/bash

./build_docker.sh && \
docker tag cesma:latest cesma:latest && \
docker push registry.ct-dev.doepke.local/cesma:latest
