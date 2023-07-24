#! /bin/bash

./build_docker.sh && \
docker tag cesma:latest registry.ct-dev.doepke.local/cesma:latest && \
docker push registry.ct-dev.doepke.local/cesma:latest
