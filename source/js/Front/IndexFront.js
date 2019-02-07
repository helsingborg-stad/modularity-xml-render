// Polyfills
import 'es6-promise';
import 'isomorphic-fetch';
// Components
import JsonParser from './Components/JsonParser';

const domElements = document.getElementsByClassName('modularity-xml-render');
const { translation } = modXMLRender;

for (let i = 0; i < domElements.length; i++) {
    const element = domElements[i];
    ReactDOM.render(
        <JsonParser
            url={element.dataset.url}
            view={element.dataset.view}
            fieldMap={JSON.parse(element.dataset.fieldMap)}
            showSearch={element.dataset.showSearch}
            showPagination={element.dataset.showPagination}
            perPage={parseInt(element.dataset.perPage)}
            translation={translation}
        />,
        element
    );
}
