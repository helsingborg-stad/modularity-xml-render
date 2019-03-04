const PostType = ({ doExport, xmlDataKeys, postTypeId, translation }) => (
    <div>
        <h3>{translation.exportToPostType}</h3>
        <div className="radio">
            <label>
                <input
                    type="checkbox"
                    value={doExport}
                    onClick={event => {
                        const showData = document.getElementById('showDataDropDown');
                        if (window.getComputedStyle(showData).display === 'none') {
                            showData.style.display = 'block';
                        } else {
                            showData.style.display = 'none';
                        }
                    }}
                />
                {translation.exportChoice}
            </label>
        </div>
    </div>
);
export default PostType;
