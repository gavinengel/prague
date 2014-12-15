// http://53greenst1-3000.terminal.com/api/blah/rev-engine/?sound=asdf
// http://docs.strongloop.com/display/public/LB/Remote+methods#Remotemethods-HTTPmappingofinputarguments
var winston = require('winston');


module.exports = function(Blah) {
  //remote method
  Blah.revEngine = function(sound, cb) {
    cb(null, sound + ' ' + sound + ' ' + sound);
  };
  Blah.remoteMethod(
    'revEngine',
    {
      accepts: [{arg: 'sound', type: 'string'}],
      returns: {arg: 'engineSound', type: 'string'},
      http: {path:'/rev-engine', verb: 'get'}
    }
  );


  //remote method - before hook
  Blah.beforeRemote('revEngine', function(context, unused, next) {
    winston.info('in Blah.beforeRemote');

    next();
  });

  //remote method - after hook
  Blah.afterRemote('revEngine', function(context, remoteMethodOutput, next) {
    next();
  });

  //model hook - before save
  Blah.beforeSave = function(next, model) {
    next();
  };
};
