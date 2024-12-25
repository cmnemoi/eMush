{ pkgs ? import <nixpkgs> {} }:

let
  php83 = pkgs.php83.buildEnv {
    extensions = { enabled, all }: enabled ++ (with all; [ xsl intl pdo pdo_pgsql opcache protobuf ]);
    extraConfig = "memory_limit=-1";
  };

  nodejs22 = pkgs.nodejs_22;

  postgresql17 = pkgs.postgresql_17;

in pkgs.mkShell {
  buildInputs = [
    php83
    php83.packages.composer
    nodejs22
    pkgs.yarn
    postgresql17
  ];

  shellHook = ''
    chmod +x $PWD/scripts/setup_postgres.sh
    chmod +x $PWD/scripts/stop_postgres.sh
    $PWD/scripts/setup_postgres.sh
    trap "$PWD/scripts/stop_postgres.sh" EXIT
  '';
}
