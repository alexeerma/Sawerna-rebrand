// Installs the test-only fixture plugin (see fixtures/sawerna-test-fixture.php) for the run.
const fs = require('fs');
const path = require('path');
const target = path.resolve(__dirname, '../../../mu-plugins/sawerna-test-fixture.php');
module.exports = async () => { fs.copyFileSync(path.join(__dirname, 'fixtures/sawerna-test-fixture.php'), target); };
module.exports.target = target;
