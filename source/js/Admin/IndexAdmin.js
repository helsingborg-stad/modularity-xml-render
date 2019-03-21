// Polyfills
import 'es6-promise';
import 'isomorphic-fetch';
// Components
import Settings from './Components/Settings';
import { translation, posttypes, setPostType } from './Config/config';

const modJsonRenderElement = 'modularity-xml-render';
const domElement = document.getElementById(modJsonRenderElement);

ReactDOM.render(
    <Settings postTypes={posttypes} translation={translation} setPostType={setPostType} />,
    domElement
);
