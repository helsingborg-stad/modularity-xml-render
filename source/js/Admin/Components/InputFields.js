const InputFields = ({ fieldMap, url, view }) => (
    <div>
        <input type="hidden" name="mod_xml_render_url" value={url} />
        <input type="hidden" name="mod_xml_render_view" value={view} />
        <input type="hidden" name="mod_xml_render_fieldmap" value={JSON.stringify(fieldMap)} />
    </div>
);

export default InputFields;
