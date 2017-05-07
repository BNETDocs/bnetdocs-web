#!/bin/bash
ssh -f -N -T -L 3306:localhost:3306 server.bnetdocs.org
#ssh -f -N -D 3306 server.bnetdocs.org
