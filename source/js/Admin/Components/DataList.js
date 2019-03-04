import ListItem from './ListItem';
import DropArea from './DropArea';
import ViewOption from './ViewOption';
import PostType from './PostType';
import { dropAreas } from '../Config/config';
import { Dropdown } from 'hbg-react';
import RecursiveIterator from 'recursive-iterator';
import objectPath from 'object-path';

class DataList extends React.Component {
    updateFieldMap(field, value) {
        this.props.updateFieldMap({ [field]: value });
    }

    setItemContainer(e, field, value) {
        e.preventDefault();
        this.updateFieldMap(field, value);
    }

    setView(e) {
        this.props.setView(e.target.value);
    }

    renderNodes(data) {
        const originalData = this.props.data;

        return Object.keys(data).map(item => {
            if (
                item === 'objectPath' ||
                (Array.isArray(data[item]) && this.props.fieldMap.itemContainer !== null)
            ) {
                return;
            }

            let sample = '';
            if (this.props.fieldMap.itemContainer !== null) {
                const containerData = objectPath.get(
                    originalData,
                    this.props.fieldMap.itemContainer
                );
                sample = objectPath.get(containerData[1], data[item]);
            }

            let child = (
                <ListItem
                    key={item.toString()}
                    field={item}
                    value={data[item]}
                    sample={sample}
                    fieldMap={this.props.fieldMap}
                    onClickContainer={e =>
                        this.setItemContainer(e, 'itemContainer', data[item].objectPath)
                    }
                    translation={this.props.translation}
                />
            );

            if (typeof data[item] === 'object' && data[item] !== null) {
                child = React.cloneElement(child, {
                    children: Array.isArray(data[item])
                        ? this.renderNodes(data[item][0])
                        : this.renderNodes(data[item]),
                });
            }

            return child;
        });
    }

    render() {
        let { data } = Object.assign({}, this.props);

        const xmldataKeys = null;
        let dropDownDataNodes = null;

        const { translation, view, doExport, postTypeId } = this.props;
        const fieldMap = this.props.fieldMap;

        if (Array.isArray(data)) {
            fieldMap.itemContainer = '';
        }

        if (fieldMap.itemContainer === null) {
            if (Array.isArray(data)) {
                data = data[0];
            }

            for (let { parent, node, key, path } of new RecursiveIterator(data)) {
                if (typeof node === 'object' && node !== null) {
                    let pathString = path.join('.');
                    objectPath.set(data, pathString + '.objectPath', pathString);
                }
            }

            return (
                <div>
                    <h3>{translation.selectItemsContainer}</h3>
                    <ul className="json-tree">{this.renderNodes(data)}</ul>
                </div>
            );
        } else {
            let objectData = objectPath.get(data, fieldMap.itemContainer);

            if (Array.isArray(objectData)) {
                objectData = objectData[0];
            }

            for (let { parent, node, key, path } of new RecursiveIterator(objectData)) {
                if (!(typeof node === 'object' && node !== null)) {
                    let pathString = path.join('.');
                    objectPath.set(objectData, pathString, pathString);
                }
            }

            dropDownDataNodes = this.renderNodes(objectData);

            let dropDownItems;
            let dropDownKeys;

            if (dropDownDataNodes !== null) {
                dropDownItems = [];
                for (let int = 0; dropDownDataNodes < int; int++) {
                    // dropDownDataNodes.map(item => dropDownItems.push(item));
                    // dropDownDataNodes.map(dropDownItems[int].props.value);
                    console.log(dropDownDataNodes[int]);
                }

                dropDownItems = Array.from(new Set(dropDownItems));
                dropDownKeys = 0;
            }

            return (
                <div className="grid nav-menus-php">
                    <div className="grid__item">
                        <h3>{translation.infoFields}</h3>
                        <p>
                            <i>{translation.dragAndDropInfo}</i>
                        </p>
                        {this.renderNodes(objectData)}
                    </div>
                    <div className="grid__item">
                        <ViewOption
                            view={view}
                            setView={this.setView.bind(this)}
                            translation={translation}
                        />

                        <PostType
                            postTypeId={postTypeId}
                            xmlDataKeys={xmldataKeys}
                            doExport={doExport}
                            translation={translation}
                        />
                        <div className="grid-md-2 grid-sm-4 dropdown showDataDropDown">
                            <Dropdown title={translation.category}>
                                {dropDownItems.map((item, index) => (
                                    <a
                                        key={'dropLink-' + dropDownKeys++}
                                        onClick={() => {
                                            /*
                                            const categoryProps = {
                                                Id: item.Id,
                                                Name: item.Name,
                                            };
                                            this.getJsonData(false, false, categoryProps);
                                            */
                                        }}
                                    >
                                        {item[index]}
                                    </a>
                                ))}
                            </Dropdown>
                        </div>

                        <div className="drop-container">
                            {dropAreas(view).map(area => {
                                return (
                                    <div key={area.id}>
                                        <h3>{area.label}</h3>
                                        <DropArea
                                            id={area.id}
                                            list={fieldMap[area.id]}
                                            itemsChange={this.updateFieldMap.bind(this)}
                                            limit={area.limit}
                                        />
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>
            );
        }
    }
}

export default DataList;
