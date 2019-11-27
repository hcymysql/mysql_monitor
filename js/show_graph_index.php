    <script type="text/javascript">
              //var  myChart = echarts.init(document.getElementById('qps'), 'shine');
	      var  myChart = echarts.init(document.getElementById('index'),'shine');
              var arr1=[],arr2=[],arr3=[];
              function arrTest(){
                $.ajax({
                  type:"post",
                  async:false,
                  //url:"get_graph_data.php",
		          url:"db_using_index_graph_getdata.php?fn=index&ip=<?php echo $ip;?>&dbname=<?php echo $dbname;?>&port=<?php echo $port;?>",
                  data:{},
                  dataType:"json",
                  success:function(result){
                    if (result) {
                      for (var i = 0; i < result.length; i++) {
                          arr1.push(result[i].create_time);
			  arr2.push(result[i].Handler_read_key);
                          arr3.push(result[i].Handler_read_rnd_next);
                      }
                    }
                  }
                })
                return arr1,arr2,arr3;
              }
              arrTest();

              var  option = {
					title: {
						text: '索引使用率图表'
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
                       data:['Handler_read_key','Handler_read_rnd_next']
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
								formatter:'{value}'
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
							name:'Handler_read_key',
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
							name:'Handler_read_rnd_next',
							type:'line',
							stack: '个',
							areaStyle: {},
							data:arr3
					   }
                    ]
                };
                // 为echarts对象加载数据
                myChart.setOption(option);
            // }
    </script>
