const PostType = ({ doExport, xmlDataKeys, postTypeId, translation, postTypes }) => (
    <div>
        <h3>{translation.exportToPostType}</h3>
        <div className="checkBox">
            <label>
                <input
                    type="checkbox"
                    value="exportToPostType"
                    onClick={event => {
                        if (event.target.checked) {
                            document.getElementById('showDataDropDown').style.display = 'block';
                        } else {
                            document.getElementById('showDataDropDown').style.display = 'none';
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
