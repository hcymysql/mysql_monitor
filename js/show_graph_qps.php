    <script type="text/javascript">
              //var  myChart = echarts.init(document.getElementById('qps'), 'shine');
	      var  myChart = echarts.init(document.getElementById('qps'),'shine');
              var arr1=[],arr2=[],arr3=[],arr4=[],arr5=[];
              function arrTest(){
                $.ajax({
                  type:"post",
                  async:false,
                  //url:"get_graph_data.php",
		          url:"db_qps_graph_getdata.php?fn=index&ip=<?php echo $ip;?>&dbname=<?php echo $dbname;?>&port=<?php echo $port;?>",
                  data:{},
                  dataType:"json",
                  success:function(result){
                    if (result) {
                      for (var i = 0; i < result.length; i++) {
                          arr1.push(result[i].create_time);
						  arr2.push(result[i].qps_select);
                          arr3.push(result[i].qps_insert);
			              arr4.push(result[i].qps_update);
                          arr5.push(result[i].qps_delete);
                      }
                    }
                  }
                })
                return arr1,arr2,arr3,arr4,arr5;
              }
              arrTest();

              var  option = {
					title: {
						text: 'QPS图表'
					},
                    tooltip: {
						trigger: 'axis',
						axisPointer: {
							type: 'cross',
						        //type:'category',
							label: {
								backgroundColor: '#6a7985'
							}
						}
                    },
                    legend: {
                       data:['select','insert','update','delete']
                    },
					grid: {
						left: '3%',
						right: '4%',
						bottom: '3%',
						containLabel: true
					},
                    xAxis : [
                        {
                            type : 'category',
							boundaryGap : false,
                            data : arr1
                        }
                    ],
                    yAxis : [
                        {
                            type : 'value',
							axisLabel: {
								formatter:'{value}(个)'
                            }
			            }
                    ],

		    dataZoom: [
      			  {   // 这个dataZoom组件，默认控制x轴。
   			        //type: 'inside',
            			type: 'slider', // 这个 dataZoom 组件是 slider 型 dataZoom 组件
				//inverse: true,
            			start: 100,      // 左边在 10% 的位置。
            			end: 80         // 右边在 60% 的位置。
       		           }
    		    ],

		    grid:{
			    x2: 60 ,
       			    bottom: "70px"
       		    },

                    series : [
                        {
							name:'select',
							type:'line',
							stack: '个',
							label: {
								normal: {
								show: false,
								position: 'top'
							}
						},
							areaStyle: {normal: {}},
							data:arr2
						},
					   {
							name:'insert',
							type:'line',
							stack: '个',
							areaStyle: {},
							data:arr3
					   },
					   {
							name:'update',
							type:'line',
							stack: '个',
							areaStyle: {},
							data:arr4
					   },					   
					   {
							name:'delete',
							type:'line',
							stack: '个',
							areaStyle: {},
							data:arr5
					   }                       
                    ]
                };
                // 为echarts对象加载数据
                myChart.setOption(option);
            // }
    </script>
