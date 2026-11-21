// Removes the test-only fixture plugin so it never stays installed.
const fs = require('fs');
const { target } = require('./global-setup');
module.exports = async () => { fs.rmSync(target, { force: true }); };
