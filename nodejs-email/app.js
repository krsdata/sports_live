const send = require('gmail-send')({
  user: 'kroy.iips@gmail.com',
  pass: 'Kandy#123!#',
  to:   'kroy@mailinator.com',
  subject: 'test subject',
});

send({
  text:    'gmail-send example 1',  
}, (error, result, fullResult) => {
  if (error) console.error(error);
  console.log(result);
})
