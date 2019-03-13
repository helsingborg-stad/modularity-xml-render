const PostType = ({ doExport, xmlDataKeys, postTypeId, translation, postTypes, setView }) => (
    <div>
        <h3>{translation.exportToPostType}</h3>
        <div className="checkBox">
            <label>
                <input
                    type="checkbox"
                    name="exportToPostType"
                    value="export"
                    onChange={setView}
                    onClick={event => {
                        if (event.target.checked) {
                            document.getElementById('showDataDropDown').style.display = 'block';
                            document.getElementById('xml-view-inputs').style.display = 'none';
                            document.querySelector('.drop-container').classList.add('export');
                        } else {
                            document.getElementById('showDataDropDown').style.display = 'none';
                            document.getElementById('xml-view-inputs').style.display = 'block';
                            document.querySelector('.drop-container').classList.remove('export');
                        }
                    }}
                />
                {translation.exportChoice}
            </label>
        </div>
        <div id="showDataDropDown" className="postTypeDropDown">
            <label>
                <select name="postType">
                    {postTypes.map(ptype => (
                        <option key={ptype} value={ptype}>
                            {ptype}
                        </option>
                    ))}
                </select>
            </label>
        </div>
    </div>
);

export default PostType;
