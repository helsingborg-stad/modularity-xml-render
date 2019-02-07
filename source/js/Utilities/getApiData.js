import axios from 'axios';

function getApiData(url) {
    return axios
        .get('/wp-json/ModularityXmlParser/v1/Get/?url=' + url)
        .then(res => res)
        .then(result => ({ result }), error => ({ error }));
}

export default getApiData;
