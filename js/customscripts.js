
async function test() {
    
    let body = new FormData();
    body.append('action', 'test_jobcart');
    body.append('time', JSON.stringify((new Date()).getTime()));
    body.append('nonce', settings.nonce);
    body.append('template', settings.templates[settings.templates.length-1]);
    
    let response = await fetch(settings.url, {
        method: 'POST',
        body: body
    });

    if (response.ok) {
        let result = await response.json();
        console.log(result);
    }
}

test();