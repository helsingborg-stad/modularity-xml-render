const InputFields = ({ fieldMap, url, view, setPostType }) => (
    <div>
        <input type="hidden" name="mod_xml_render_url" value={url} />
        <input type="hidden" name="mod_xml_render_view" value={view} />
        <input type="hidden" name="mod_xml_render_fieldmap" value={JSON.stringify(fieldMap)} />
        <input type="hidden" id="setPostType" name="setPostType" value={setPostType} />
    </div>
);

export default InputFields;
