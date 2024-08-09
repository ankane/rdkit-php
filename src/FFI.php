<?php

namespace RDKit;

class FFI
{
    public static $lib;

    private static $instance;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            // https://github.com/rdkit/rdkit/blob/master/Code/MinimalLib/cffiwrapper.h
            self::$instance = \FFI::cdef('
                // I/O
                char *get_mol(const char *input, size_t *mol_sz, const char *details_json);
                char *get_qmol(const char *input, size_t *mol_sz, const char *details_json);
                char *get_molblock(const char *pkl, size_t pkl_sz, const char *details_json);
                char *get_v3kmolblock(const char *pkl, size_t pkl_sz, const char *details_json);
                char *get_smiles(const char *pkl, size_t pkl_sz, const char *details_json);
                char *get_smarts(const char *pkl, size_t pkl_sz, const char *details_json);
                char *get_cxsmiles(const char *pkl, size_t pkl_sz, const char *details_json);
                char *get_cxsmarts(const char *pkl, size_t pkl_sz, const char *details_json);
                char *get_json(const char *pkl, size_t pkl_sz, const char *details_json);
                char *get_rxn(const char *input, size_t *mol_sz, const char *details_json);
                char **get_mol_frags(const char *pkl, size_t pkl_sz, size_t **frags_pkl_sz_array, size_t *num_frags, const char *details_json, char **mappings_json);

                // substructure
                char *get_substruct_match(const char *mol_pkl, size_t mol_pkl_sz, const char *query_pkl, size_t query_pkl_sz, const char *options_json);
                char *get_substruct_matches(const char *mol_pkl, size_t mol_pkl_sz, const char *query_pkl, size_t query_pkl_sz, const char *options_json);

                // drawing
                char *get_svg(const char *pkl, size_t pkl_sz, const char *details_json);
                char *get_rxn_svg(const char *pkl, size_t pkl_sz, const char *details_json);

                // calculators
                char *get_descriptors(const char *pkl, size_t pkl_sz);
                char *get_morgan_fp(const char *pkl, size_t pkl_sz, const char *details_json);
                char *get_morgan_fp_as_bytes(const char *pkl, size_t pkl_sz, size_t *nbytes, const char *details_json);
                char *get_rdkit_fp(const char *pkl, size_t pkl_sz, const char *details_json);
                char *get_rdkit_fp_as_bytes(const char *pkl, size_t pkl_sz, size_t *nbytes, const char *details_json);
                char *get_pattern_fp(const char *pkl, size_t pkl_sz, const char *details_json);
                char *get_pattern_fp_as_bytes(const char *pkl, size_t pkl_sz, size_t *nbytes, const char *details_json);
                char *get_topological_torsion_fp(const char *pkl, size_t pkl_sz, const char *details_json);
                char *get_topological_torsion_fp_as_bytes(const char *pkl, size_t pkl_sz, size_t *nbytes, const char *details_json);
                char *get_atom_pair_fp(const char *pkl, size_t pkl_sz, const char *details_json);
                char *get_atom_pair_fp_as_bytes(const char *pkl, size_t pkl_sz, size_t *nbytes, const char *details_json);
                char *get_maccs_fp(const char *pkl, size_t pkl_sz);
                char *get_maccs_fp_as_bytes(const char *pkl, size_t pkl_sz, size_t *nbytes);

                // modification
                short add_hs(char **pkl, size_t *pkl_sz);
                short remove_all_hs(char **pkl, size_t *pkl_sz);

                // standardization
                short cleanup(char **pkl, size_t *pkl_sz, const char *details_json);
                short normalize(char **pkl, size_t *pkl_sz, const char *details_json);
                short neutralize(char **pkl, size_t *pkl_sz, const char *details_json);
                short reionize(char **pkl, size_t *pkl_sz, const char *details_json);
                short canonical_tautomer(char **pkl, size_t *pkl_sz, const char *details_json);
                short charge_parent(char **pkl, size_t *pkl_sz, const char *details_json);
                short fragment_parent(char **pkl, size_t *pkl_sz, const char *details_json);

                // coordinates
                void prefer_coordgen(short val);
                short has_coords(char *mol_pkl, size_t mol_pkl_sz);
                short set_2d_coords(char **pkl, size_t *pkl_sz);
                short set_3d_coords(char **pkl, size_t *pkl_sz, const char *params_json);
                short set_2d_coords_aligned(char **pkl, size_t *pkl_sz, const char *template_pkl, size_t template_sz, const char *details_json, char **match_json);

                // housekeeping
                // use void *ptr instead of char *ptr
                void free_ptr(void *ptr);

                // other
                char *version();
                void enable_logging();
                void disable_logging();

                // chirality
                short use_legacy_stereo_perception(short value);
                short allow_non_tetrahedral_chirality(short value);

                // logging
                void *set_log_tee(const char *log_name);
                void *set_log_capture(const char *log_name);
                short destroy_log_handle(void **log_handle);
                char *get_log_buffer(void *log_handle);
                short clear_log_buffer(void *log_handle);
            ', self::$lib ?? Vendor::defaultLib());
        }

        return self::$instance;
    }
}
