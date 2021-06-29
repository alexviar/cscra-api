<?php

use App\Http\Controllers\EmpleadorController;
use App\Http\Controllers\AseguradosController;
use App\Http\Controllers\EspecialidadesController;
use App\Http\Controllers\ListaMoraController;
use App\Http\Controllers\MedicosController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PrestacionController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\RegionalesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SolicitudAtencionExternaController;
use App\Http\Controllers\UnidadesTerritorialesController;
use App\Http\Controllers\UserController;
use App\Models\Asegurado;
use App\Models\Galeno\AfiliacionBeneficiario;
use App\Models\Galeno\AfiliacionTitular;
use App\Models\Galeno\Afiliado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::get("/foto", function (){
//   $binary = pack("H*", "ffd8ffe000104a46494600010101006000600000ffdb004300080606070605080707070909080a0c140d0c0b0b0c1912130f141d1a1f1e1d1a1c1c20242e2720222c231c1c2837292c30313434341f27393d38323c2e333432ffdb0043010909090c0b0c180d0d1832211c213232323232323232323232323232323232323232323232323232323232323232323232323232323232323232323232323232ffc000110800fa00c803012200021101031101ffc4001f0000010501010101010100000000000000000102030405060708090a0bffc400b5100002010303020403050504040000017d01020300041105122131410613516107227114328191a1082342b1c11552d1f02433627282090a161718191a25262728292a3435363738393a434445464748494a535455565758595a636465666768696a737475767778797a838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae1e2e3e4e5e6e7e8e9eaf1f2f3f4f5f6f7f8f9faffc4001f0100030101010101010101010000000000000102030405060708090a0bffc400b51100020102040403040705040400010277000102031104052131061241510761711322328108144291a1b1c109233352f0156272d10a162434e125f11718191a262728292a35363738393a434445464748494a535455565758595a636465666768696a737475767778797a82838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae2e3e4e5e6e7e8e9eaf2f3f4f5f6f7f8f9faffda000c03010002110311003f00a2d4cef4f634c35f3e7eb48954e3b0a783fe71512d482910d0e1fe78a51d7a0a6d385048e1f4a507fce29a2945492381ff0038a507e9f9534714b4c43b3f4a33f4a406973c5210b9e7ff00ad4b9fa7e54dc8f514a0e690585ce71fe14e1fe78a677a7834992c78fc3f2a703f4fca9838a7678a821813f4fca93bf6fca969053403c13edf953b3f4fca9829c4d49204f1dbf2aace7e6edf954e6abbe73570dcb82d49a23c8e9f9514c84fcc28a24b5266b5339a9878a79a61ad8ef4390d482a25a956a4990e14e1cd369c28218b4b9c567df6a70d9a9190f2e384cf7f7ac2fed9bc91cf9721c13fc4063f0f4ad234a52d4e3af8da341da6f53a1d46ff00ec30ac80062c71835058ebb05d23f9b889d4f03a861ea2b9f98cd711b49333c9216e73dbf0f4a8dd7ca5518c03dfdeb78e1e36b3dcf26a66efda5e0bdd37a5d764f38a416eaea0e325bf5a826f11b29da96eacfdf2c48aa52b8fb10dbc21eb8fe2a486386181a59987bb1e9f4154a9416e8e59e69886f476261af5f373e5c2bede5fff005ea68bc473c6479d0230ee5495fe79ac692fe10df22123f9fe94c1751b9e7e5cfaf4aaf6516b6338e63885f6cec2db5db29c805cc6c7b3f4fceb511830041041e98af3d29139dd1900fb1ab765ab5de9ec003be3cfdc6e86b19e1d7d93d1a19adddaaaf9a3bb14b5474fd460d422dd136187de42795abb9ae2945a7667ad19292bc5dd0a6802909a01a431e294d203452244350c9c9a9b35039e6aa25c771d17de145245f780a29b14f7281a61eb4f34d35a9da80548bc5442a4535226480d616adae9b576b7b71ba61d48e76fff005eb52f27fb3da48f9c1c607d6b91d87cf040df3b9cf1fc3fe7d6b7a10527791e3e698d7421c90f8980918b8799b6e7921d783ef9ad4484ab0c448c1c65594e41acc9da474310905c63b38c11f4355eda69ad3f74e2430139287aa1fef29ec6bb5a563e55c9b7a9b84c57036a30571f7181efe87deaadc159212adf7b18c7a1aae1da4937120bf79178de3dc55f8e17670ef1960dd78acdb48b516ca33850114f112f5e7b28aab793493cc03f08b80a9e95b17567f7700050c091ea33ff00d7acb9ad256ce07dec9c8a2134395368cb779249bca872474047f155b4b596260b36e56238571c1fa1a54b39a2720060a3a81c135a96f2a5cc42dee32a1b842dd41f4cd6aea2e865c8ca2d6e36ef43c0ebed5179c54e09240ed565434176f6d2e7a63eb54a55018f63d0d2dc69d8b905d3c32acf0b9595791cf0dec6bb3d2b5987528f6ffabb851f3447f98f515e7aac571dc7f3a9a1bc92cef5658db0cadb973fcab2ab454d799e860f1b2a32b3f84f4eed4a2aa58dec77f671dc467871c8fee9ee2ad0af3249a7667d326a4aeb61c0d3a9b4b5220355dea726a073cd544b8847d4514919f985154c25b94c9a6b53a9a4d59d6868eb522d47d0d3d4d00ccdd618e065880a323eb59b6b664c7f31dbbb05cfb765ab9aab0676cf237f4fa566fdb5a69fc9420283ce2bba9c7dc563e2730a9ed31327f22d4f6e8a9b5088e3ef8feb5456d7cd9047116393c54d3cc252b121e0719aec7c2da02c8ab2b2e47a9a9a95142376634a929b20d0fc1f2dd2ab3e00cf0715db5a783ede38c070323a15addb0b6486350140038e95a0c4015c9cf29eacd275145f2c51c9dcf85ace460c530ca3f84707f0acb5f09c11cac36e63ea01aed9f1bf39a825504f150eeb66691a97dce2af3c356c2325230a7e95ca5ee88bbdb682ac39e3b1af50bb4ca1e2b99beb61b8be2b3552507b9d518c671d51e7b7f013346e7ef0e0d635d821d8fbe0d75faac212e5091f2b75ae7af6d4fef0e0fca6bd2a3539a299e7d6a2e2f432b76234c83907a0ef4c932c376071dc9c548613b4b6ddc9dfd69550061df3eddaba6e8e44743e0cbc22e26b52df2baee519ee3ae3f0aecc579969ce2c357b79b25555c73e95e98ad9507a579f8b85a5ccba9f4f95d473a3cafa0fa334034135c87a221350b9a90d44c6ad171409f7851421e45155609150f34d34a69299d486d394f4a6e6954fcc3eb40cc0d625e0f231b89fd6b9f495b0553ab1e6b67510ad04e09f9973803eb55ecac93f764e0b3649e7a0ff00f5d7a316ac7e7f5eeea4bd49345b192e2fa257c9dcd819af71d26c52d2da3403000af31f0c5a09b56403e6e724d7ae44155064e38ae1c54b9a691d14bdda7ea5a4cf6e82a420919a8a39d003d38a57bb8c719e6b3ba464d49bd10d718e2a3d87ad2b4eac7ad24d308f07b5276344a5b15a74c0e6b22fa057538eb8a9b51d52384fcce140e79ae62ffc556ea4846cd63cbcef447642f0576676b1699837f04ab62a9369e25b2de402afde8b8d696ec951d0f502921be3026c07e5feed744615231b09d48b77396b8416f2b230c60f22a848fb1fdbb5741ad46b710b5cc430cbf787b5734ee1fa7e75e8d37cc8f36ac3925a6c4edfbd46da304722bd0f48b9fb5e976d31fbcc8037d4707f9579c5b3e24da7f8862bb4f09b37f67ca87eeac876ff005acb131bc0f53289b551c7ba3a1cd19a4a335e79f41611aa16383529e950b5345c4543c8a284ea28a612dcaa7b521a534d35674210d28a4a075a451cd5ca96babe8f1c6368e7be7355e29fcb4ce47231f8558d5c18750986302402453f863f9d63976765553d5b15e8c758a3e0713070af38beecf4cf05c6b05acfa8cb9258ed53eb5d47f694f265a352703d2aae8da60b7d0ed60c74404e6aedc5cdb6996e1e43924e1547526bc99d4e69b67746168a48c4babdd5a5918471cea9ea3a566f9baa24844b2c847a135a771e23ba9602f6f6e04386c48e76a1207203104b1fa0ac4b5d5ef2fda498c0c638c02db5b2707d88156e33e5ba438ca3cdca74da3dd5d34a048491915d3dea9fb206039c566e9b6605ac738c10e0104771eb5b93e1ec173e959ad9dcceac929ab1e51aecf3bdc3293939c75aa16ba3dbbfef6f64214fbd6eead6e1f507ee334f3a4c735d4723c692daa4657ca66ea4f7c7f8d5c25eedaf63a2693dcca92e7c316abb1325c1c64293cfd6989269b72710c8ad9eddea293c3d78fa82999d9a05da0360e768e831d33818a9e7d0a4bbbe33a45e511e8b8ad9a87493318f3754326b38c215403691c8ae1b54b36b1bc2a46118f06bd42df4891132ee0e2b9ef15e9424b17900f9e339f7a742b72cecd8ab5353869d0e1e3e1837a74af45d0edc5be9509230f22ef3f8d7036b6e667f2cb61b2063d7dabd351046811780a30056f8b96891d393d3d65363f3413499a09ae13ddb084f06a163ce2a56e95093cd3348a1e879145247f7a8a052dcac690d19a4279aa3a00d1de928a06666b969e7d9f9c3868413f506b9ed321f36fadc1181e7203f89aebeed04b692c7c9debb78f7ac658c45ad5baa2858cc91a800703041ae9a353dd713e6b39c32552355753db20b5dd0851d862a1b8f0e25f37fa43653180a3b8f7f6abd6b20da3a62b5611b80626b82104f53ccab5a706ec60dcf87ede6d3058dc468f0281b40c82b8e8462a8d9787acec331c106416cfce7249aeaee5951724e0555b688c927984600e99ad9eba110aaece4c85e1f26344c638ed524c98b2519ed4b740f9a29f3a9164011c9a94b562e6f859c16a7185b966f7a75a1ca0156752b73bc93c1aad6671260f1586973d27a9a56f02bf5e2ad0b389471442a000453ae26f2d4e29bd08d5b28dd22c5c67ad731ab279d04a87904115b971397272738ac0bc392dcf045386f72ad6385b3871a8c4a0127785fc41aefab98d3b4f61ae79873b1033fe7c0ae9457662249b477e5949c29c9beac766933484d266b9cf4ac0c78a88f5a7b1e2a22699a4512c679a29a87a514c992d4ab9a09a4cd2d33a03349451da8017eb59572a61d4addbf84c8a7f106b56a2b8b617014e70e84153574e5cb2d4e1cc30eebd2b4775a9ea16927c8873915b09761500c571fa3dd3bdb2072776d15bb1480e0935cb0972bb1f375a95dea693fef704d4cae11401552293919a9b76f3c77add3b9c728f433b56d5ed34c85ee2ee611a8e013550788e1bbd356e2394347b7838c7e60d5ad6b4bb7d4a35dff002c89f74e33fa5655de9b6d1d915954bb101771eb5124fb9d54a34dc55d6a725acf8982dc2ed47906ee420ce07bd5cb1d416f191a304617904524de1f8a08cf70c7249a759dac5681844b8e7ae739a1c60969b9d4f53a3b797318f7a8ae5c95c1aa70dd8e8a46452cb31624935cf26ee11451b96c1f6ac5b862d230ea2b4ee240c4d65cc70cc6b6a4b518b6eaa3e6039c62a7a8608d9132e4163d876a90d5cb73dac3c1c69a4c5cd21a4341348e8b08c78a889a7b1a8c1e6997144aa7a5148a7914504b2b63a51467a51546e1451486801c29c87047a5301c528a4267571b46a91344fc141dfa115af6b3ee1ef5c459ce619979e0f06bafb29159158573544d3b9f3d8aa1ece4d3ea6d23f439a9c4b804d5189b38c532e2e0420966c0aa84ecae798e9ddd8b6ce1c939e959f7b35b48815e6c60f5c645604dabdf6a2e61d3eda478c672dd01fc4d34e89e20bb8fe610c4a3b96cff002ade31948da34e31d5b2d5e6a96d1c66d90f9ac7f002b21f508002a7e5cf18a91fc29a8805db5288381fdcef58f2f87ae2dc317d47739ff6335a3a71eacd138f434e3ba56cf96c1bdc1eb567cedf0f279ae7adec24b29777da4b8eb8c62b4d27dc8735cf56296c09ea12be0919aa72106455f53524920fc2b1eeb578ac2f621229656c8623aafbd5d283e85c651524e6ec8dbcd2678a6472a4f12c91387461c30a750d3ea7d0269aba0c9a42693341a0ab08c6a31d69cc78a62d3b16b624079a29abd451412c869693341a0d45a69a5a2800cd381a65432dc285f94f1ebeb5518b91cf89c4d3a11bcc8354d456d20600fcd8fd6ba6f0aeba97b671316e586d61fdd61d4579ceaae64f309270a38f7ab3e0fb8923bd92d54fcb22799f88ff00eb1adaa5052a4fba3e56a636556bde5b33daa29c03c9e2a29592e5f61195ef9ae7edafe464d8c791d6b52ca42cdc0e08eb5e5f2b8ee6f65ba2e3dda5be142aa803000158fab6b372d1948a7655f406b55ec9e52483c9aa67c346e64ccce553af15a41ca4f50bc2272ab7f71e61dd72ed9e3ad453de4e665762768c807d6baa7f0ed9472303cfa6e38e2b1afecedc4e914630a8bdfd49ff00f556f78a6529dd198b3bcbcb702a4662a870704d5a36d1c4b918e2b32eee55338229ab49e845fb8c9ae36a1c9e95c8ea3334b7ef9238c0fa56d3ce6e25da3a0e4d73f7ca53529c1fefe7f0c577508a4ce0c554bc743434ed527b063b0e633d54f20d75963a8c17e998db1201f3467a8ff1ae055f9e4d5886578a55923665901e0838c51568a96bd4df0598ce87baf589df93486b374cd516f97649859d4723b37b8ad0ae2945c5d99f5746ac2b414e0f4118e053474a56349e948dd6c3d4f228a41da8a096402973483df803d6a19eed20257059bb7a55284a5b115f154a82bd4762c0a865b848ce3058fa0aa50ea4f2bb038c0ec053d11cee9243b73c05f4add514be23c4af9cca5a5156247b96287a007ad417126d0b81c0e05364c13d71f4ac75d48b6bff0066988113031a7fb2dd8ff9f5ad630be88f1eb626527cd51ddb1d7437478c671939f5149e1b7306b76cb8c29660c7d46318fd68915d64dcc3e506adda45b2d96e631cdbdc82e7d15d703f503f3aa7ac5a315f1a3b69e329891383eb562c752f2e4024f969d66c27b519e411c552bab564cb01915e568f467a7aa3b182fa39230c18631da966d4d2319ce7d857091dfcf6dc2484a9fe13da9b36b12b75dc288d295f417bbd4e8e6d503bc8ee428ec2b9cb9d443dc315e72726a8cd78ce492c79aa6f27391fceb68d1ee12a896c5ebbd4709d7ad614f3bcadc75ed52485a41ebe9535bd99cef7ade3150463294a7a2d84b3b7d8996e49ac1d50635290f7c0aeb0c7b1335cb6ab6f2c1aa4b1cdf2b8c12a7aaf1d0fa1ad68b6e5739f129460914573b87ad684516501e86abc111328e09cf000ee6b48dbcd6ced1b853b4ed250ee50de99ee477c56f26eda1c90dc8235649032921d4e720f435d3e9d7c6ea229211e72f5ff00687ad7037b3486f2544918286c000d4fa7dcdc5bdc2fef5b6fd6a2a51e68dd9db82cc5e1ea69b753d08d20ac38b5a646d92aefc77c806b423d420908058a93fde1c7e75c2e9c91f594731c3d5da567e65e07a514d520e08208f5068a9b1d7a3d519ef312d9760981c2e6a09dd04630d9e2b295b24673f5ab510c279cc328ac01527afb57a0e365a1f073ad29bbcb5658b481a353295f988e83b55ac34a0ee461e9822b9bb9d56e7ce6423cb5078029b07da6f77b2cf24689d76f539a6e9b7a99aab15a23a094c1003bdd4003279c9ae2b529d66d45e688e0672a7f1eb5b2f0a2204c16f76e49fad51b9b48f664000ff2ad295a2eecc6bc9cd591a71ccb7f69f6ae724fcea3b377ad3f0f6a71e9baa2bcb12cb6728f2e647195653d8d739605ed64054fcadf797b30f435a5708b1344d102223c8c1fd2a6492765b151939475dcf5f3a07d8adc5e69ecd71a6c8370eef0fb37a8f7fceaab5a8c9656f95bb76359bf0f7c691e9aeba7dfcc56d89c4729e89ecdfecfbf6af48d47c3115eafda74e2b1bb0dc62cfc8fee0f6fe55c35f0d7f7a07552c6727bb576ee79c5de951cb92060d63dc696e87209aee25b292291a29a368e45ea8c391503e9e1fb5717b49c3467a4b964ae8e09eca404839fcaa1366fd315dccba5e4f41512e8e0f2471f4ad1570714725069e78661c7615712d892005f6c574634b6791618a32cec70aaa3926bacd33c3d6be1fb47d4b522866890b9eeb1003f56ad69f355f439ab568d15e6701ab5b7fc22fa425e5ca8fed3b838b480f3e5fac8c3d4761eb8af3a681e67696462cec72cec72493d49ae8bc43ab5c7887589afe7c853f2c487a469d87f8fbd63c8bf2ec4e82bba2945591e7ca529be6911a40060138f434cbfba6b655591ce506d8d3fbb49a95d496b6cbb0ed924e17be07735cfbbb33ef762c7deb585372d5ec653aaa0acb7245059bd49eb57a0420863c01c9a65bc398965e8a7be7a53bcf591b6213b077f5ab93be84415b564eac5e4c9f5ab11ce477247a55645f738a781818f5ac8dd49a34a2bf785b74431cf201eb45558907068a9e54f7475d3c5568ab464d08bc106b4adb6be9d2af70435642b6e3cd684726cb4900ef8154d1c7068cc9630f29c9eb5a362ab123a8fe2c66a8b365b9c66b42342b1066e33da9bdac4c37229f00e49efc71559886ed9c54971217248e80f4a8236c127af0463da9207b88ca31f2638ab76d3830ed71b947de51d71ea3dc557d994c938e69b13795267f3f7a76be824eccd18e2f2fe6465646e8dd8d771e0ff001eea1e1e2b6d20377603ac4c798c7aa9edf4e95e7e4bdb3f9b0e1e36e591b90dff00d7f7ab10ddc72a3490ee057ef46c7257fc47bd4eab545b5192b33e99825d1fc5fa62dcdb481c0e03af0f11f423fa57397fa54fa64db671946384940e1bfc0fb5795786bc4d7be1ed522bbb72de5b1db2447eecabe9f5fe55f41e9fa8596bfa547326d96dee10300c3a83ebef4aad2a75d59e8fb99c2ad4c2bd3589c314527a7353da69f3df4c2281371ee4f45f735d07fc22a05ee527c5a1e769e5c7b0ff001adeb7b686d21114118451e9dfdcd7352cb9def5363a6b667151fddeaff2336c348b6d2a22e007b8230d211cfd07a0af3ef89bac32c31e911b61a5c4b281fddcf03f1233f857a85cb456d6f2dcdc3623894bb7b00335f3ef89ae27d52eee3539248d5ee1c9588365d507038ec0631935d52872e8958e2a127526e52773989586e23b552bdb95b483795dcedc229feb5348e96e8d239f91464b7f4fad60dcdcbde4be63fca070883f847f8d3842efc8dea4f9569b90bbbccfe64ac59cfaf6a6f96585380f9b156123ae86d2d0e64b9b72ba2bba08f7b6c072173c66af5bdbed419eb4b146a1ba55a5008aca52b9ac6234050714b8e7a50739a78538073c541a92210a39eb453738208e9ef45495cc5788e40383c55ce7ec81bb31354edc101dbd2b4982ac31c78e55003f5ef56dd8ce2558215dde63f4ec3d6a4966dec73d7d29a7238a8f196f7a371dec18f5a88637e3f9558276af3f85551f2499e94ee4b2c95c8fa542d11e58678ab11b866e94e9232eac57d2a7d47b896ee4c44123154e7df6f702785b695ff3835694144e9cf7a8e54de3914ee0f62dda5d2dd44c23001eaf09e47d56bd47e14788992e26d1e66f94e658727bff0010febf9d78b32bc243a12aca720835bfa3ead35a5ddbea96db44f6f206751ebfe068692d44df32b33eb046dea0d3b8158da0eb106aba5dbde427f77320751dc7b7e078ad6675552cc40006493d8574c2a271d4f3a517176386f8a3afff0066680b631b0f3ef1b047a20e4feb815e1f26a1726d1ad91f10bbef6181963ee7a91edd2b73c6de21ff008487c457178a71029f2a119fe05eff0089c9fc6b89d56f3c987ca4c7992f53e82b9e5efccf429c553a7a94b50bcfb5cfe5a7faa8cf1fed1f5aac074f5a224f4a9c27cdf4ad3e1564657727763523f981ab6100a48a2dd20ab1b006acdbbb348c46ac7dfb54c170b9a00c28a1db0383f5a469b111396e3a77a90676f151a8cbe01e33d6a7031c6293043477a295ce118d14ac3224c35c4708e013963ec39356e49373923bd55b304cd3ccc3850117f1ebfcaa563c67b55684ad81b1c9cd2aafc992393eb4dfbd85f5a900c0fe428b011c8dcf0381555c73d3ad5a946d4c93d7f4aad276e7ad084c911b047e95612623e9dea9af41eb4ffd68b0d32cbb0238e9463e53518e303f2a76f20631c52b0ca732ee1ce3834b6327933e71f291861ea2964fbfc6054614a480e38cd52dac475b9ecdf0b35e315bdce92ec5fcb3e7dbff00ba7861f81c1fc6baff001df881b47f07ccc1f17379fb88c03d011f31fc07f315e23e12d5c691e24b39e4622357dac7fd86e1bf2ce7f0adff00885adbeabac8823706dad17cb460782dd58ff9f4a5b12e9f34efd0e2ae6e12da26918e703803f957385de7999df96739ab17d71f699f0a73129f97dfde9204c9cd6d15caafd48a92737cab624890a81c54c179a7228e3a7e35288f9acdb2d21d1210453db2ac4fe54e2021e4f22a2924cb81ed506bb21e1c03823ad358f6ee6a3192d9a9d076edd6810c8d4922a63d39e3de9a30071da9af28181fce921ec36463b5ba743453243fba73ea28aa4896cb0a9e55b2a81cb9de7f1a1b0303f3ab1201f271fc351b0001e292655ba10a9e69e0ee2053d80c1e052c406fe9d8d36c4326dbc0c702aab8cf2064d5b7e95130018714903d4ae99cf3d6a55233ec680067a0a7a81c71564899fca86cf4e69ea06d3c523f55fa54752ba15dc6250076a561b978c53a5037741428185e2a89ea3914cb342a8790c0fd077a8b58be25cd9c4d8ff9e841e9fecff8d6869ea3edabc0fb8d5cec7f34ae4f24b1c93f5a71d5ebd05524d474ea35549200156a25c0c50806fe83a55c81465781f955c9910561522180cc40f6a98290a703e5cf5a56006de3b5498055781d4564f7374ac5731bbc9cf4f5a8e489c4878e3b7357481fbbe3a83fceab4bfeb4fd69260d0088803a714bdb8c67eb4d703278a6e06c6e3bd2b8324e7a8e6a27077648c669ca067a54138f9aa97625ed72491731b0dcb9231c9a2a0c7228a6ae80ffd9");
//   Storage::put("foto.jpg", $binary);
// });

/**
 * User routes
 */
Route::middleware("auth:sanctum")->get("usuarios", [UserController::class, "index"]);
Route::middleware("auth:sanctum")->get("usuarios/{id}", [UserController::class, "show"]);
Route::middleware("auth:sanctum")->post("usuarios", [UserController::class, "store"]);
// Route::middleware("auth:sanctum")->put("usuarios/cambiar-contrasena", [UserController::class, "changePassword"]);
Route::middleware("auth:sanctum")->put("usuarios/{id}/cambiar-contrasena", [UserController::class, "changePassword"]);
Route::middleware("auth:sanctum")->put("usuarios/{id}/bloquear", [UserController::class, "disable"]);
Route::middleware("auth:sanctum")->put("usuarios/{id}/desbloquear", [UserController::class, "enable"]);
Route::middleware("auth:sanctum")->put("usuarios/{id}", [UserController::class, "update"]);

/**
 * Roles routes
 */
Route::middleware("auth:sanctum")->get("roles", [RoleController::class, "index"]);
Route::middleware("auth:sanctum")->get("roles/{id}", [RoleController::class, "show"]);
Route::middleware("auth:sanctum")->post("roles", [RoleController::class, "store"]);
Route::middleware("auth:sanctum")->put("roles/{id}", [RoleController::class, "update"]);
Route::middleware("auth:sanctum")->delete("roles/{id}", [RoleController::class, "destroy"]);

/**
 * Permisos routes
 */
Route::middleware("auth:sanctum")->get("permisos", [PermissionController::class, "index"]);

Route::middleware("auth:sanctum")->get("empleadores", [EmpleadorController::class, "buscar"]);
Route::middleware("auth:sanctum")->get("empleadores/buscar-por-patronal", [EmpleadorController::class, "buscarPorPatronal"]);
Route::middleware("auth:sanctum")->get("asegurados", [AseguradosController::class, "buscar"]);

Route::middleware("auth:sanctum")->get("lista-mora", [ListaMoraController::class, "buscar"]);
Route::middleware("auth:sanctum")->post("lista-mora/agregar", [ListaMoraController::class, "agregar"]);
Route::middleware("auth:sanctum")->post("lista-mora/quitar", [ListaMoraController::class, "quitar"]);

Route::middleware("auth:sanctum")->get("especialidades", [EspecialidadesController::class, "buscar"]);
Route::middleware("auth:sanctum")->get("especialidades/{id}", [EspecialidadesController::class, "ver"]);
Route::middleware("auth:sanctum")->post("especialidades", [EspecialidadesController::class, "registrar"]);
Route::middleware("auth:sanctum")->put("especialidades/{id}", [EspecialidadesController::class, "actualizar"]);
Route::middleware("auth:sanctum")->delete("especialidades/{id}", [EspecialidadesController::class, "eliminar"]);
Route::middleware("auth:sanctum")->post("especialidades/importar", [EspecialidadesController::class, "importar"]);

Route::middleware("auth:sanctum")->get("solicitudes-atencion-externa", [SolicitudAtencionExternaController::class, "buscar"]);
Route::middleware("auth:sanctum")->post("solicitudes-atencion-externa", [SolicitudAtencionExternaController::class, "registrar"]);
Route::middleware("auth:sanctum")->get("formularios/dm11/{numero}", [SolicitudAtencionExternaController::class, "verDm11"])
  ->where('id', '[0-9]{10}')->name("forms.dm11");
Route::middleware("auth:sanctum")->put("solicitudes-atencion-externa/{id}/generar-dm11", [SolicitudAtencionExternaController::class, "generarDm11"])
  ->where('id', '[0-9]{10}');

Route::middleware("auth:sanctum")->get("medicos", [MedicosController::class, "buscar"]);
Route::middleware("auth:sanctum")->get("medicos/{id}", [MedicosController::class, "mostrar"]);
Route::middleware("auth:sanctum")->post("medicos", [MedicosController::class, "registrar"]);
Route::middleware("auth:sanctum")->put("medicos/{id}/cambiar-estado", [MedicosController::class, "cambiarEstado"]);
Route::middleware("auth:sanctum")->put("medicos/{id}", [MedicosController::class, "actualizar"]);
Route::middleware("auth:sanctum")->delete("medicos/{id}", [MedicosController::class, "eliminar"]);

Route::middleware("auth:sanctum")->get("regionales", [RegionalesController::class, "obtener"]);

Route::middleware("auth:sanctum")->get("prestaciones", [PrestacionController::class, "buscar"]);
Route::middleware("auth:sanctum")->get("prestaciones/buscar-nombre", [PrestacionController::class, "buscarPorNombre"]);
Route::middleware("auth:sanctum")->get("prestaciones/{id}", [PrestacionController::class, "ver"]);
Route::middleware("auth:sanctum")->post("prestaciones", [PrestacionController::class, "registrar"]);
Route::middleware("auth:sanctum")->put("prestaciones/{id}", [PrestacionController::class, "actualizar"]);
Route::middleware("auth:sanctum")->delete("prestaciones/{id}", [PrestacionController::class, "eliminar"]);
Route::middleware("auth:sanctum")->post("prestaciones/importar", [PrestacionController::class, "importar"]);


Route::middleware("auth:sanctum")->get("proveedores", [ProveedorController::class, "buscar"]);
Route::middleware("auth:sanctum")->get("proveedores/{id}", [ProveedorController::class, "mostrar"]);
Route::middleware("auth:sanctum")->post("proveedores", [ProveedorController::class, "registrar"]);
Route::middleware("auth:sanctum")->get("proveedores/{idProveedor}/contratos", [ProveedorController::class, "buscarContrato"]);
Route::middleware("auth:sanctum")->get("proveedores/{idProveedor}/contratos/{id}", [ProveedorController::class, "verContrato"]);
Route::middleware("auth:sanctum")->post("proveedores/{idProveedor}/contratos", [ProveedorController::class, "registrarContrato"]);
Route::middleware("auth:sanctum")->put("proveedores/{idProveedor}/contratos/{id}/consumir", [ProveedorController::class, "consumirContrato"]);
Route::middleware("auth:sanctum")->put("proveedores/{idProveedor}/contratos/{id}/extender", [ProveedorController::class, "extenderContrato"]);
Route::middleware("auth:sanctum")->put("proveedores/{idProveedor}/contratos/{id}/anular", [ProveedorController::class, "anularContrato"]);
Route::middleware("auth:sanctum")->put("proveedores/{idProveedor}/contacto", [ProveedorController::class, "actualizarInformacionContacto"]);
Route::middleware("auth:sanctum")->put("proveedores/{id}", [ProveedorController::class, "actualizar"]);
Route::middleware("auth:sanctum")->get("proveedores/buscar-nombre", [ProveedorController::class, "buscarPorNombre"]);

Route::middleware("auth:sanctum")->get("departamentos", [UnidadesTerritorialesController::class, "getDepartamentos"]);
Route::middleware("auth:sanctum")->get("provincias", [UnidadesTerritorialesController::class, "getProvincias"]);
Route::middleware("auth:sanctum")->get("municipios", [UnidadesTerritorialesController::class, "getMunicipios"]);


