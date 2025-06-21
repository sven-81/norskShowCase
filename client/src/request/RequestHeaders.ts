export function getHeaders(token: string) {
    const version: string = 'HTTP/2'
    const content: string = 'application/json'

    return {
        'Version': version,
        'Content-Type': content,
        'Authorization': token,
    };
}
