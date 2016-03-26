#!/bin/sh

copy ./conf/secret.common.ini ./conf/secret.local

mkdir -m 555 temp

rm -R temp/*

