import FieldSelection from './FieldSelection';
import InputFields from './InputFields';
import { DragDropContext } from 'react-dnd';
import HTML5Backend from 'react-dnd-html5-backend';

class Settings extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            setPostType: '',
            showFieldSelection: false,
            url: '',
            view: 'posttype',
            isLoaded: false,
            error: null,
            items: [],

            fieldMap: {
                itemContainer: null,
                heading: [],
                content: [],
            },
        };
    }

    componentDidMount() {
        this.initOptions();
        this.initSetPostType();
    }

    initOptions() {
        if (typeof modXMLRender.options !== 'undefined') {
            const options = modXMLRender.options;
            this.setState({
                url: options.url ? options.url : '',
                view: options.view ? options.view : 'posttype',
                fieldMap: options.fieldMap
                    ? JSON.parse(options.fieldMap)
                    : {
                          itemContainer: null,
                          heading: [],
                          content: [],
                      },
                showFieldSelection: !!options.url,
            });
        }
    }

    initSetPostType() {
        if (this.props.setPostType !== 'post' && this.props.setPostType !== '') {
            const setPostType = this.props;
            this.setState({
                setPostType: setPostType,
            });
        }
    }

    urlChange(event) {
        this.setState({ url: event.target.value });
    }

    handleSubmit(event) {
        event.preventDefault();
        this.setState({ showFieldSelection: true });
    }

    resetOptions(event) {
        event.preventDefault();
        this.setState({
            error: null,
            isLoaded: false,
            showFieldSelection: false,
            url: '',
            items: [],
            fieldMap: { itemContainer: null, heading: [], content: [] },
        });
    }

    updateFieldMap(value) {
        const newVal = Object.assign(this.state.fieldMap, value);
        this.setState({ fieldMap: newVal });
    }

    setError(error) {
        this.setState({ error });
    }

    setLoaded(value) {
        this.setState({ isLoaded: value });
    }

    setItems(items) {
        this.setState({ items: items });
    }

    setView(value) {
        this.setState({ view: value });
    }

    setPostType(value) {
        this.setState({ setPostType: value });
        document.getElementById('setPostType').value = value;
    }

    render() {
        const { translation, postTypes } = this.props;
        const { showFieldSelection, url, view, error, isLoaded, items, setPostType } = this.state;

        if (showFieldSelection) {
            return (
                <div>
                    <FieldSelection
                        url={url}
                        view={view}
                        setView={this.setView.bind(this)}
                        error={error}
                        setError={this.setError.bind(this)}
                        isLoaded={isLoaded}
                        setLoaded={this.setLoaded.bind(this)}
                        items={items}
                        setItems={this.setItems.bind(this)}
                        fieldMap={this.state.fieldMap}
                        updateFieldMap={this.updateFieldMap.bind(this)}
                        translation={translation}
                        postTypes={postTypes}
                        setPostType={this.setPostType.bind(this)}
                        selectExportSettings={setPostType}
                    />
                    <InputFields {...this.state} />
                    <p>
                        <a href="#" onClick={this.resetOptions.bind(this)} className="button">
                            {translation.resetSettings}
                        </a>
                    </p>
                </div>
            );
        } else {
            return (
                <div className="wrap">
                    <form onSubmit={this.handleSubmit.bind(this)}>
                        <p>
                            <label>
                                <strong>API URL</strong>
                            </label>
                            <br />
                            <i>{translation.validJsonUrl}</i>
                        </p>
                        <input
                            type="text"
                            className="large-text"
                            value={url}
                            onChange={this.urlChange.bind(this)}
                        />
                        <p>
                            <input
                                type="submit"
                                className="button button-primary"
                                value={translation.sendRequest}
                            />
                        </p>
                    </form>
                    <InputFields {...this.state} />
                </div>
            );
        }
    }
}

export default DragDropContext(HTML5Backend)(Settings);
