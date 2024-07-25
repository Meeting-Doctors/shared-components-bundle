#!/usr/bin/env bash

method="$1"
shift

exitIfInvalidExitCode() {
    if [ $1 -ne 0 ]
    then
        exit $1
    fi
}

checkNC() {
    echo "Checking $3..."

    maxcounter=50
    counter=1

    while ! nc -w 1 -z $1 $2 ; do
        sleep 1
        counter=`expr $counter + 1`
        if [ $counter -gt $maxcounter ]; then
            >&2 echo "We have been waiting for $3 too long. Failing."
            exit 1
        fi;
    done

    echo "$3 is up"
}

checkMySQL() {
    checkNC mysql 3306 "MySQL"
}

dependenciesUp() {
    echo "Dependencies up"
    checkMySQL
}

dependenciesDown() {
    echo "Dependencies down"
}

generateCoverage() {
    php vendor/bin/phpcov merge build/coverage --html build/coverage/merged/html
    exitIfInvalidExitCode $?
}

integration() {
    dependenciesUp
    php vendor/bin/phpunit --testsuite=integration --no-coverage $*
    exitIfInvalidExitCode $?
    dependenciesDown
}

integrationCoverage() {
    dependenciesUp
    php vendor/bin/phpunit --testsuite=integration $*
    exitIfInvalidExitCode $?
    dependenciesDown
}

unit() {
     dependenciesUp
    php vendor/bin/phpunit --testsuite=tests --no-coverage $*
    exitIfInvalidExitCode $?
}

unitCoverage() {
    dependenciesUp
    php vendor/bin/phpunit --testsuite=tests $*
    exitIfInvalidExitCode $?
}

testAll() {
    unit
    integration
}

testAllCoverage() {
    unitCoverage
    integrationCoverage
    generateCoverage
}

case "$method" in
  all)
    testAll
    ;;
  integration)
    integration $*
    ;;
  unit)
    unit $*
    ;;
  all-coverage)
    testAllCoverage
    ;;
  integration-coverage)
    integrationCoverage $*
    ;;
  unit-coverage)
    unitCoverage $*
    ;;
  *)
    testAll
esac

exit 0