# Set vars
echo "Setting variables"
source build.cfg
cd ..
CURRENT_DIR=${PWD##*/}
FULL_DIR=${PWD}

PLUGIN_FILE_NAME=$CURRENT_DIR-$PLUGIN_VERSION

# Delete files early so they're not copied
rm -rf vendor
rm -rf node_modules

# Make a temp directory and clone files into it
echo "Making a temp directory and cloning files into it"
cd ..
rm -rf "${CURRENT_DIR}_temp"
mkdir "${CURRENT_DIR}_temp"
cp -L -R $CURRENT_DIR "${CURRENT_DIR}_temp/${CURRENT_DIR}"
cd "${CURRENT_DIR}_temp/${CURRENT_DIR}"

# Renaming files
find . -type f -name "{{ TEXT_DOMAIN }}*" -print0 | while read -r -d '' file; do
    mv "$file" "${file//\{\{ TEXT_DOMAIN \}\}/$TEXT_DOMAIN}"
done

# Pre find/replace cleanup
echo "Initial cleanup"
# rm -rf vendor
# rm -rf node_modules
rm -rf .git
rm -rf .idea
rm -rf bin
rm -rf .editorconfig
rm -rf .gitignore
find . -type f -name '.DS_Store*' -delete
find . -type f -name '*.sketch' -delete
find . -type f -name '*.old' -delete

# Set the plugin variables
echo "Doing find/replace"
find . -type f -name '*.*' | xargs perl -pi -w -e "s/{{ PLUGIN_NAME }}/$PLUGIN_NAME/g;"
find . -type f -name '*.*' | xargs perl -pi -w -e "s/{{ PLUGIN_DESCRIPTION }}/$PLUGIN_DESCRIPTION/g;"
find . -type f -name '*.*' | xargs perl -pi -w -e "s/{{ PLUGIN_VERSION }}/$PLUGIN_VERSION/g;"
find . -type f -name '*.*' | xargs perl -pi -w -e "s/{{ PLUGIN_AUTHOR }}/$PLUGIN_AUTHOR/g;"
find . -type f -name '*.*' | xargs perl -pi -w -e "s/{{ PLUGIN_AUTHOR_URI }}/$PLUGIN_AUTHOR_URI/g;"
find . -type f -name '*.*' | xargs perl -pi -w -e "s/{{ PLUGIN_WEBSITE }}/$PLUGIN_WEBSITE/g;"
find . -type f -name '*.*' | xargs perl -pi -w -e "s/{{ PLUGIN_STORE_URL }}/$PLUGIN_STORE_URL/g;"
find . -type f -name '*.*' | xargs perl -pi -w -e "s/{{ PLUGIN_STORE_ID }}/$PLUGIN_STORE_ID/g;"
find . -type f -name '*.*' | xargs perl -pi -w -e "s/{{ TEXT_DOMAIN }}/$TEXT_DOMAIN/g;"
find . -type f -name '*.*' | xargs perl -pi -w -e "s/{{ PLUGIN_WP_TESTED_UP_TO }}/$PLUGIN_WP_TESTED_UP_TO/g;"
find . -type f -name '*.*' | xargs perl -pi -w -e "s/{{ PLUGIN_MINIMUM_WP }}/$PLUGIN_MINIMUM_WP/g;"
find . -type f -name '*.*' | xargs perl -pi -w -e "s/{{ PLUGIN_MINIMUM_PHP }}/$PLUGIN_MINIMUM_PHP/g;"
find . -type f -name '*.*' | xargs perl -pi -w -e "s/{{ PLUGIN_MINIMUM_BB }}/$PLUGIN_MINIMUM_BB/g;"

# Remove development code
echo "Removing development-only code"
LC_ALL=C find . -type f -name '*.*' -exec sed -i '' '/{{ DEVELOPMENT }}/d' {} +

# Install & compile 3rd-party libraries
echo "Installing & compiling 3rd-party libraries"
yarn && yarn run build
composer install --optimize-autoloader --no-dev

# Remove unnecessary files
echo "Post process cleanup"
find . -type d -name '.git' -exec rm -rf "{}" \;
rm -rf node_modules
rm -rf res/src
rm -rf yarn.lock
rm -rf yarn-error.log
rm -rf webpack.config.js
rm -rf composer.json
rm -rf composer.lock
rm -rf package-lock.json
rm -rf package.json
rm -rf phpcs.xml.dist
rm -rf phpunit.xml.dist
rm -rf .babelrc
rm -rf .travis.yml
rm -rf .eslintrc
rm -rf .csslintrc
rm -rf .php_cs.cache

# Zip up the directory
echo "Zipping final build"
cd ..
zip -r -X ${PLUGIN_FILE_NAME}.zip ${CURRENT_DIR}

# Move zip to desktop and cleanup
rm -rf ~/Desktop/${PLUGIN_FILE_NAME}.zip
mv ${PLUGIN_FILE_NAME}.zip ~/Desktop/${PLUGIN_FILE_NAME}.zip
cd ${FULL_DIR}
cd ..
rm -rf "${CURRENT_DIR}_temp"
cd ${CURRENT_DIR}
composer update
yarn && yarn run build-dev

echo "Complete. ${PLUGIN_FILE_NAME}.zip is on the desktop"
